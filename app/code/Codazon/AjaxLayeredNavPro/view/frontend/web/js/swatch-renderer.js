/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';
    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            _init: function () {
                this._super();
                this._EmulateSelected(this._getActiveParams());
            },
            _getActiveParams : function() {
                var activeParams = {};
                $('[data-activefilter]').each(function(i, e) {
                    var activefilter = $(this).data('activefilter');
                    $.each(activefilter, function(code, value) {
                        activeParams[code] = parseInt(value);
                    });
                });
                return activeParams;
            }
        });
        return $.mage.SwatchRenderer;
    };
});
