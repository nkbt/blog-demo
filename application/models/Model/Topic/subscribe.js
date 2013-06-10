'use strict';


/**
 * @type {Helpers}
 */
var helpers;


/**
 * @param {Object} eventData
 */
var commentIncr = function (eventData) {
    helpers.validateRequired(eventData, ['node.entity.id_topic'], function (error) {
        if (error === null) {
            helpers.incr("Counter:Topic:" + eventData.node.entity.id_topic + ":Comment", eventData);
        }
    });
};


/**
 * @param {Object} eventData
 */
var commentDecr = function (eventData) {
    helpers.validateRequired(eventData, ['node.entity.id_topic'], function (error) {
        if (error === null) {
            helpers.decr("Counter:Topic:" + eventData.node.entity.id_topic + ":Comment", eventData);
        }
    });
};


exports.php = [
    "onDelete:User",
    "onRestore:User"
];


/**
 * @param {Helpers} initHelpers
 */
exports.subscribe = function (initHelpers) {
    helpers = initHelpers;

    helpers.getEventEmitter()
        .on("onInsert:Comment", commentIncr)
        .on("onRestore:Comment", commentIncr)
        .on("onDelete:Comment", commentDecr);

};