'use strict';

var glob = require("glob");
var redis = require('redis');
var http = require('http');
var path = require('path');
var querystring = require('querystring');

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

eventEmitter.on('error', function onErrorListener(error, data) {
    var date = new Date(),
        id = 'Log:NodeError:' + date.getUTCFullYear() + '-' + date.getUTCMonth() + '-' + date.getUTCDate();

    client.hset(id, error.message, JSON.stringify({
        'message': error.message,
        'utc': date.toUTCString(),
        'local': date.toJSON(),
        'stack': error.stack,
        'data': data
    }));
    client.lpush("Log:NodeErrorFlat", date.toJSON() + "\n" + error.message + "\n" + "Full id: " + id);
    //console.log("Error\n", error.message, data, "\n\n");
});

eventEmitter.on('message', function onErrorListener(message, data) {
    var date = new Date(),
        id = 'Log:NodeMessage:' + date.getUTCFullYear() + '-' + date.getUTCMonth() + '-' + date.getUTCDate();

    client.hset(id, message.message, JSON.stringify({
        message: message.message,
        utc: date.toUTCString(),
        local: date.toJSON(),
        stack: message.stack,
        data: data
    }));

//	client.lpush("Log:NodeErrorFlat", date.toJSON() + "\n" + error.message + "\n" + "Full id: " + id);
    //console.log("Error\n", error.message, data, "\n\n");
});
eventEmitter.on('counter', function onCounterListener(logId, direction, count) {
    var date = new Date(),
        id = 'Log:NodeSubscriber:' + date.getUTCFullYear() + '-' + date.getUTCMonth() + '-' + date.getUTCDate();
    client.lpush(id, JSON.stringify({
        'id': logId,
        'count': count,
        'direction': direction,
        'utc': date.toUTCString(),
        'local': date.toJSON()
    }));
    client.lpush("Log:NodeSubscriberFlat", date.toJSON() + "\n" + logId + " / " + direction + " / " + count + "\n" + "Full id: " + id);
});


var client = redis.createClient();
var pubsubClient = redis.createClient();

pubsubClient.subscribe('onInsert:Redis_Another');
pubsubClient.on("message", function onMessage(channel, message) {
    var json, date = new Date();

    try {
        json = JSON.parse(message);
    } catch (exc) {
        eventEmitter.emit('error', new Error('Problem with message decoding: ' + message), exc);
        return;
    }

    //console.log('NodePubSub\n', date.toJSON(), channel, json.node, "\n\n");
    client.lpush("Log:NodePubSub:" + channel, date.toJSON() + "\n" + message);

    eventEmitter.emit(channel, client, json.node, json.php);
});

var executePhpCallback = function (target, channel, data) {
    var options, request, postData, date = new Date(),
        eventData = channel.split(':');

    postData = querystring.stringify({data: data});

    options = {
        hostname: '127.0.0.1',
        port: 80,
        path: '/api/event?' + "event=" + eventData[0] + "&target=" + target + "&source=" + eventData[1],
        method: 'POST',
        timeout: 10000,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Content-Length': postData.length
        }
    };

    //console.log('Node2PhpCallback\n', date.toJSON(), options.path + "?" + postData, "\n\n");
    client.lpush("Log:Node2PhpCallback:" + channel, date.toJSON() + "\n" + target + "\n" + postData);

    request = http.request(options, function onHttpRequest(res) {
        var responseString = '';
        res.setEncoding('utf8');
        res.on('data', function (chunk) {
            responseString += chunk;
        });
        res.on('end', function () {
            try {
                var response = JSON.parse(responseString);
                //console.log('Node2PhpCallback.onHttpRequest\n', date.toJSON(), "\n\n", responseString);
                if (res.statusCode !== 200 || !response.ok) {
                    eventEmitter.emit('error', new Error('Problem with request: ' + options.path), response);
                }
            } catch (exc) {
                eventEmitter.emit('error', new Error('Problem with request: ' + options.path + '. JSON decoding failed!'), {'exc': exc, 'response': responseString});
            }
        });
    });

    request.on('error', function onError(exc) {
        eventEmitter.emit('error', new Error('Problem with request: ' + options.path), exc);
    });

    request.write(postData);
    request.end();
};


var subscribe = function (subscriberScript) {
    var subscriber = require(subscriberScript),
        php = subscriber.php || [],
        entityName = path.relative("../application/models/Model/", path.dirname(subscriberScript)).replace(/[\\\/]/g, '_');

    subscriber.subscribe(eventEmitter);
    php.forEach(function (eventName) {
        eventEmitter.on(eventName, function (redisClient, data, phpData) {
            executePhpCallback(entityName, eventName, phpData);
        })
    });
};

glob.sync("../application/models/Model/**/*.js").forEach(subscribe);
console.log("Events\n", eventEmitter.getEvents(), "\n\n");
pubsubClient.subscribe.apply(pubsubClient, eventEmitter.getEvents());