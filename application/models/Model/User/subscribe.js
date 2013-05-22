'use strict';


exports.php = [
];

/**
 * @param {EventEmitter} eventEmitter
 * @param {RedisClient} redisClient
 * @param id
 */
var incr = function (eventEmitter, redisClient, id) {
    return redisClient.incr(id, function (error, data) {
        if (error !== null) {
            eventEmitter.emit('error', new Error('redisClient.incr returned error: ' + error.message), {'id': id, 'data': data, 'error': error});
        } else {
            eventEmitter.emit('counter', id, 'incr', data);
        }
    });
};


/**
 * @param {EventEmitter} eventEmitter
 * @param {RedisClient} redisClient
 * @param id
 */
var decr = function (eventEmitter, redisClient, id) {
    return redisClient.decr(id, function (error, data) {
        if (error !== null) {
            eventEmitter.emit('error', new Error('redisClient.decr returned error: ' + error.message), {'id': id, 'data': data, 'error': error});
        } else {
            eventEmitter.emit('counter', id, 'decr', data);
        }
    });
};

/**
 * @param {EventEmitter} eventEmitter
 * @param data
 */
var topicIsValid = function (eventEmitter, data) {
    if (data.entity === undefined) {
        eventEmitter.emit('error', new Error('data.entity is not defined'), data);
        return false;
    }
    if (data.entity.idUser === undefined) {
        eventEmitter.emit('error', new Error('idUser is not defined'), data);
        return false;
    }
    return true;
};


/**
 * @param {EventEmitter} eventEmitter
 * @param {RedisClient} redisClient
 * @param data
 */
var topicIncr = function (eventEmitter, redisClient, data) {
    if (!topicIsValid(eventEmitter, data)) {
        return;
    }

    incr(eventEmitter, redisClient, "Counter:User:" + data.entity.idUser + ":Topic");
};


/**
 * @param {EventEmitter} eventEmitter
 * @param {RedisClient} redisClient
 * @param data
 */
var topicDecr = function (eventEmitter, redisClient, data) {
    if (!topicIsValid(eventEmitter, data)) {
        return;
    }

    decr(eventEmitter, redisClient, "Counter:User:" + data.entity.idUser + ":Topic");
};


/**
 * @param {EventEmitter} eventEmitter
 */
exports.subscribe = function (eventEmitter) {

    eventEmitter.on("onInsert:Topic", topicIncr.bind(null, eventEmitter));
    eventEmitter.on("onRestore:Topic", topicIncr.bind(null, eventEmitter));
    eventEmitter.on("onDelete:Topic", topicDecr.bind(null, eventEmitter));

};