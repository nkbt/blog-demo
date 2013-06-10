'use strict';


/**
 * @type {Helpers}
 */
var helpers;


/**
 * @param {Object} eventData
 */
var topicIncr = function (eventData) {
    helpers.validateRequired(eventData, ['node.entity.id_user'], function (error) {
        if (error === null) {
            helpers.incr("Counter:User:" + eventData.node.entity.id_user + ":Topic", eventData);
        }
    });
};


/**
 * @param {Object} eventData
 */
var topicDecr = function (eventData) {
    helpers.validateRequired(eventData, ['node.entity.id_user'], function (error) {
        if (error === null) {
            helpers.decr("Counter:User:" + eventData.node.entity.id_user + ":Topic", eventData);
        }
    });
};


exports.php = [
    "onDelete:Topic",
    "onRestore:Topic"
];


/**
 * @param {Helpers} initHelpers
 */
exports.subscribe = function (initHelpers) {

    helpers = initHelpers;

    helpers.getEventEmitter()
        .on("onInsert:Topic", topicIncr)
        .on("onRestore:Topic", topicIncr)
        .on("onDelete:Topic", topicDecr);
};