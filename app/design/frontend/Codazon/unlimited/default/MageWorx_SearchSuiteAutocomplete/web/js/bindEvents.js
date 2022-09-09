/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'uiRegistry',
    'mageUtils'
], function ($, Component, registry, utils) {
    'use strict';

    return Component.extend({
        defaults: {
            minSearchLength: 2
        },

        initialize: function () {
            this._super();

            utils.limit(this, 'load', this.searchDelay);
            var self = this;
            $(this.inputSelector).each(function() {
                self.$input = $(this);
                self.$input.unbind('input')
                    .on('input', $.proxy(self.load, self))
                    .on('input', $.proxy(self.searchButtonStatus, self))
                    .on('focus', $.proxy(self.showPopup, self));
            
            });
            $(document).on('click', $.proxy(this.hidePopup, this));   
            //$(document).ready($.proxy(this.load, this));
            window.windowLoaded ? this.load() : $(window).on('load', $.proxy(this.load, this));
            $(document).ready($.proxy(this.searchButtonStatus, this));
        },

        load: function (event) {
            var self = this;
            var searchText = self.$input.val();

            if (searchText.length < self.minSearchLength) {
                return false;
            }

            registry.get('searchsuiteautocompleteDataProvider', function (dataProvider) {
                var $cat = self.$input.parents('form').first().find('[name=cat]');
                dataProvider.searchText = searchText;
                dataProvider.cat = $cat.length ? $cat.val() : false;
                dataProvider.load();
            });
        },

        showPopup: function (event) {
            var self = this,
                searchField = $(self.inputSelector),
                searchFieldHasFocus = searchField.is(':focus') && searchField.val().length >= self.minSearchLength;

            registry.get('searchsuiteautocomplete_form', function (autocomplete) {
                autocomplete.showPopup(searchFieldHasFocus);
            });
        },

        hidePopup: function (event) {
            if ($(this.searchFormSelector).has($(event.target)).length <= 0) {
                registry.get('searchsuiteautocomplete_form', function (autocomplete) {
                    autocomplete.showPopup(false);
                });
            }
        },

        searchButtonStatus: function (event) {
            var self = this,
                searchField = $(self.inputSelector),
                searchButton = $(self.searchButtonSelector),
                searchButtonDisabled = (searchField.val().length > 0) ? false : true;

            searchButton.attr('disabled', searchButtonDisabled);
        },

        spinnerShow: function () {
            var spinner = $(this.searchFormSelector);
            spinner.addClass('loading');
        },

        spinnerHide: function () {
            var spinner = $(this.searchFormSelector);
            spinner.removeClass('loading');
        }

    });
});
