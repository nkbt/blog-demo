/**
 * @var {EventEmitter}
 */
var eventEmitter;

/**
 * @var {RedisClient}
 */
var redisClient;

/**
 * @param {EventEmitter} initEventEmitter
 * @param {RedisClient} initRedisClient
 */
exports.init = function (initEventEmitter, initRedisClient) {
    eventEmitter = initEventEmitter;
    redisClient = initRedisClient;
};

var Helpers = {


    /**
     * @param {String} id
     * @param {Object} eventData
     */
    incr: function (id, eventData) {
        redisClient.incr(id, function (error, data) {
            this.logEvent(error, data, "Increment on " + id, eventData);
        }.bind(this));

        return this;
    },


    /**
     * @param {String} id
     * @param {Object} eventData
     */
    decr: function (id, eventData) {
        redisClient.decr(id, function (error, data) {
            this.logEvent(error, data, "Decrement on " + id, eventData);
        }.bind(this));

        return this;
    },


    /**
     * @param {Object} eventData
     * @param {Array} required
     * @param {Function} callback
     */
    validateRequired: function (eventData, required, callback) {
        required.forEach(function (term) {

            try {
                term.split('.')
                    .reduce(function (result, key) {
                        result.path.push(key);
                        if (result.eventData[key] === undefined) {
                            throw new Error('Key ' + result.path.join('.') + ' not found');
                        }
                        result.eventData = result.eventData[key];
                        return result;
                    }, {path: [], eventData: eventData});
                callback(null);
            } catch (err) {
                this.logEvent(err, {'required': required}, "Validation", eventData);
                callback(err);
            }
        }.bind(this));
    },


    /**
     * @returns {EventEmitter}
     */
    getEventEmitter: function () {
        return eventEmitter;
    },


    /**
     * @returns {RedisClient}
     */
    getRedisClient: function () {
        return redisClient;
    },


    /**
     * @param {Error|null} error
     * @param {Object} data
     * @param {String} message
     * @param {Object} eventData
     */
    logEvent: function (error, data, message, eventData) {
        var date = new Date(), logInfo = {}, id;

        logInfo.message = message;
        logInfo.utc = date.toUTCString();
        logInfo.local = date.toJSON();

        if (eventData && eventData.node !== undefined) {
            logInfo.eventData = eventData.node;
        }

        if (data) {
            logInfo.data = data;
        }


        if (error !== null) {
            logInfo.error = {
                message: error.message,
                stack: error.stack
            };
            logInfo.message = "FAILED - " + logInfo.message;
        }

        if (eventData && eventData.ident !== undefined) {
            id = eventData.ident;
        } else {
            id = "Error";
        }

        redisClient.rpush(id, JSON.stringify(logInfo));
    }

};

exports.Helpers = Helpers;


