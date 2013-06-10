'use strict';

var glob = require("glob");
var redis = require('redis');
var http = require('http');
var path = require('path');
var querystring = require('querystring');
var functions = require('./functions');
/**
 * @type {Helpers}
 * */
var helpers = functions.Helpers;

var events = require('events');

var EventEmitter = events.EventEmitter;
EventEmitter.prototype.getEvents = function () {
    var eventName, events = [];
    for (eventName in this._events) {
        if (this._events.hasOwnProperty(eventName)) {
            events.push(eventName);
        }
    }
    return events;
};

var eventEmitter = new EventEmitter();
var client = redis.createClient();
var pubsubClient = redis.createClient();

pubsubClient.on("message", function onMessage(channel, message) {
    var json;

    try {
        json = JSON.parse(message);
    } catch (exc) {
        helpers.logEvent(
            exc,
            {channel: channel, message: message},
            "PubSub message JSON decoding failed"
        );
        return;
    }

    eventEmitter.emit(channel, json);
});

var executePhpCallback = function (target, channel, eventData) {
    var options, request, postData;

    postData = querystring.stringify({data: eventData.php});

    options = {
        hostname: '127.0.0.1',
        port: 80,
        path: '/api/event?'
            + querystring.stringify({
            event: channel.split(':')[0],
            target: target,
            source: channel.split(':')[1],
            ident: eventData.ident
        }),
        method: 'POST',
        timeout: 10000,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Content-Length': postData.length
        }
    };

    helpers.logEvent(null, options, 'Node to PHP call', eventData);

    request = http.request(options, function onHttpRequest(res) {
        var responseText = '';
        res.setEncoding('utf8');
        res.on('data', function (chunk) {
            responseText += chunk;
        });
        res.on('end', function () {
            try {
                var response = JSON.parse(responseText.trim());
                if (res.statusCode !== 200 || !response.ok) {
                    helpers.logEvent(
                        new Error("PHP side application error"),
                        {options: options, response: response},
                        'Node to PHP call request',
                        eventData
                    );
                }
            } catch (exc) {
                helpers.logEvent(
                    new Error("Response JSON decoding failed"),
                    {options: options, responseText: responseText},
                    'Node to PHP call request',
                    eventData
                );
            }
        });
    });

    request.on('error', function onError(error) {
        helpers.logEvent(
            error,
            {options: options},
            'Node to PHP call request',
            eventData
        );
    });

    request.write(postData);
    request.end();
};


var subscribe = function (subscriberScript) {
    var subscriber = require(subscriberScript),
        php = subscriber.php || [],
        entityName = path.relative("../application/models/Model/", path.dirname(subscriberScript)).replace(/[\\\/]/g, '_');

    subscriber.subscribe(functions.Helpers);
    php.forEach(function (eventName) {
        eventEmitter.on(eventName, executePhpCallback.bind(null, entityName, eventName));
    });
};

functions.init(eventEmitter, client);

glob.sync("../application/models/Model/**/*.js").forEach(subscribe);
pubsubClient.subscribe.apply(pubsubClient, eventEmitter.getEvents());


var server = http.createServer(function handler(req, res) {
    fs.readFile(__dirname + '/index.html',
        function (err, data) {
            if (err) {
                res.writeHead(500);
                return res.end('Error loading index.html');
            }

            res.writeHead(200);
            return res.end(data);
        });
});
var io = require('socket.io')
    .listen(server);
var fs = require('fs');
var async = require('async');

server.listen(3000);

var checkRedis = function (socket, eventsCount) {

    var date = new Date(),
        day = date.getDate() + "",
        month = (date.getMonth() + 1) + "",
        year = date.getFullYear() + "",
        id = 'Event:' + year + '-' 
            + (month.length == 1 ? "0" + month : month) + "-"
            + (day.length == 1 ? "0" + day : day) + ":*";
    
    async.waterfall([

        client.keys.bind(client, id),

        function (keys, callback) {
            async.map(
                keys,
                client.llen.bind(client),
                function (error, data) {

                    var newEventsCount = data.reduce(function (count, accum) {
                        return accum + count
                    }, 0);


                    if (newEventsCount <= eventsCount) {
                        return callback(new Error('No new items added'));
                    }
                    eventsCount = newEventsCount;

                    return callback(error, keys.map(function (key, index) {
                        return {key: key, length: data[index]}
                    }));

                }
            );
        },

        function (data, callback) {

            async.map(
                data,
                function (item, next) {
                    client.lrange(item.key, 0, item.length, function(error, log) {
                        next(error, {key: item.key, log: log});
                    });
                },
                callback
            );

        }
    ], function (err, result) {
        if (err === null) {
            result.forEach(function(item) {
                socket.emit('event log', item);
            });
        }
        setTimeout(checkRedis.bind(null, socket, eventsCount), 2000);
    });

};

io.sockets.on('connection', function (socket) {
    checkRedis(socket, 0);
});




