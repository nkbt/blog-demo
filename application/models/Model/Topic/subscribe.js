'use strict';


exports.php = [
    "onDelete:User"
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
var commentIsValid = function (eventEmitter, data) {
    if (data.entity === undefined) {
        eventEmitter.emit('error', new Error('data.entity is not defined'), data);
        return false;
    }
    if (data.entity.idTopic === undefined) {
        eventEmitter.emit('error', new Error('idTopic is not defined'), data);
        return false;
    }
    return true;
};


/**
 * @param {EventEmitter} eventEmitter
 * @param {RedisClient} redisClient
 * @param data
 */
var commentIncr = function (eventEmitter, redisClient, data) {
    if (!commentIsValid(eventEmitter, data)) {
        return;
    }

    incr(eventEmitter, redisClient, "Counter:Topic:" + data.entity.idTopic + ":Comment");
};


/**
 * @param {EventEmitter} eventEmitter
 * @param {RedisClient} redisClient
 * @param data
 */
var commentDecr = function (eventEmitter, redisClient, data) {
    if (!commentIsValid(eventEmitter, data)) {
        return;
    }

    decr(eventEmitter, redisClient, "Counter:Topic:" + data.entity.idTopic + ":Comment");
};


/**
 * @param {EventEmitter} eventEmitter
 */
exports.subscribe = function (eventEmitter) {

    eventEmitter.on("onInsert:Comment", commentIncr.bind(null, eventEmitter));
    eventEmitter.on("onRestore:Comment", commentIncr.bind(null, eventEmitter));
    eventEmitter.on("onDelete:Comment", commentDecr.bind(null, eventEmitter));

};