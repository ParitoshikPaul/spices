/**
 * Copyright © 2020 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery", "jquery-ui-modules/widget", "domReady!",
],function ($) {
    $.widget('codazon.firstLoad', {
        options: {
            formKeyInputSelector: 'input[name="form_key"]'
        },
        _checkVisible: function() {
            var $element = this.element;
            var cond1 = ($element.get(0).offsetWidth > 0) && ($element.get(0).offsetHeight > 0),
            cond2 = ($element.is(':visible'));
            var winTop = $(window).scrollTop(),
            winBot = winTop + window.innerHeight,
            elTop = $element.offset().top, elHeight = $element.outerHeight(true),
            elBot = elTop + elHeight;
            var cond3 = (elTop <= winTop) && (elBot >= winTop),
            cond4 = (elTop >= winTop) && (elTop <= winBot), cond5 = (elTop >= winTop) && (elBot <= winBot),
            cond6 = (elTop <= winBot) && (elBot >= winBot), cond7 = true;
            if ($element.parents('md-tab-content').length) {
                cond7 = $element.parents('md-tab-content').first().hasClass('md-active');
            }
            return cond1 && cond2 && (cond3 || cond4 || cond5 || cond6) && cond7;
        },
        _create: function() {
            var self = this, conf = this.options;
            this._bindEvents();
        },
        _bindEvents: function(html) {
            var self = this;
            this._checkVisible() ? this._ajaxFirstLoad(html) : setTimeout(function() {
                self._bindEvents(html);
            }, 50);
        },
        _ajaxFirstLoad: function(html) {
            var self = this, conf = this.options;
            if (html) self._attachHtml(html);
            else $.ajax({url: conf.ajaxUrl, type: 'get', cache: true, data: conf.jsonData, success: function(html) {
                self._attachHtml(html);
            }});
        },
        _attachHtml: function(html) {
            var self = this, conf = this.options, formKey = $(conf.formKeyInputSelector).first().val();
            self.element.html(html).removeClass('no-loaded');
            $('body').trigger('contentUpdated');
            self.element.find('[name="form_key"]').each(function() {
                var $field = $(this).val(formKey);
            });
            $('body').trigger('ajaxProductFirstTimeLoaded');
        }
    });
    return $.codazon.firstLoad;
});
