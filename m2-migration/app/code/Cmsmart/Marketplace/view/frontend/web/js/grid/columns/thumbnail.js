define([
    './column',
    'jquery'
], function (Column, $) {
    'use strict';

    return Column.extend({
        defaults: {
            fieldClass: {
                'cmsmart-marketplace-grid-thumbnail-cell': true
            }
        }
    });
});
