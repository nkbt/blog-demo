'use strict';


/**
 * @type {Helpers}
 */
var helpers;


exports.php = [
    "onDelete:Topic",
    "onRestore:Topic"
];


/**
 * @param {Helpers} initHelpers
 */
exports.subscribe = function (initHelpers) {
    helpers = initHelpers;
};