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

server.listen(3000);

io.sockets.on('connection', function (socket) {
    socket.emit('news', { hello: 'world' });
    socket.on('my other event', function (data) {
        console.log(data);
    });
});




