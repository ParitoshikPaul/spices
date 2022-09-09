/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global $break $ FORM_KEY */

define([
    'underscore',
    'Magento_Ui/js/lib/view/utils/async',
    'mage/template',
    'uiRegistry',
    'prototype',
    'Magento_Ui/js/form/element/abstract',
    'jquery/colorpicker/js/colorpicker',
    'jquery/ui'
], function (_, jQuery, mageTemplate, rg, prototype, Abstract) {
    'use strict';

    /**
     * Former implementation.
     *
     * @param {*} value
     * @param {Object} container
     * @param {String} uploadUrl
     * @param {String} elementName
     */
    function oldCode(value, container, uploadUrl, elementName, imgUrl) {
        //swatchVisualOption.initColorPicker();
        var $preview = jQuery(container).find('.swatch_window');
        var addBackground = function(value) {
            var backgroundImage = imgUrl + value;
            var imgObj = new Image();
            container.addClassName('unavailable');
            $preview.css({
                backgroundImage: '',
                backgroundSize: ''
            });
            jQuery(imgObj).load(function() {
                container.removeClassName('unavailable');
                $preview.css({
                    backgroundImage: 'url(' + backgroundImage + ')',
                    backgroundSize: 'cover'
                });
            });
            if (!_.isEmpty(value)) {
                imgObj.src = backgroundImage;
            }
        }
        
        if (_.isEmpty(value)) {
            container.addClassName('unavailable');
        } else {
            addBackground(value);
        }
        var $input = jQuery(container).prev('input');
        $input.on('change', function(){
            addBackground($input.val());
        });
        
        jQuery('body').on('click', function (event) {
            var element = jQuery(event.target);

            if (
                element.parents('.swatch_sub-menu_container').length === 1 ||
                element.next('div.swatch_sub-menu_container').length === 1
            ) {
                return true;
            }
            jQuery('.swatch_sub-menu_container').hide();
        });

        jQuery(function ($) {

            var swatchComponents = {

                /**
                 * div wrapper for to hide all evement
                 */
                wrapper: null,

                /**
                 * iframe component to perform file upload without page reload
                 */
                iframe: null,

                /**
                 * form component for upload image
                 */
                form: null,

                /**
                 * Input file component for upload image
                 */
                inputFile: null,

                /**
                 * Create swatch component for upload files
                 *
                 * @this {swatchComponents}
                 * @public
                 */
                create: function () {
                    this.wrapper = $('<div>').css({
                        display: 'none'
                    }).appendTo($('body'));

                    this.iframe = $('<iframe />', {
                        name: 'upload_iframe_' + elementName
                    }).appendTo(this.wrapper);

                    this.form = $('<form />', {
                        name: 'swatch_form_image_upload_' + elementName,
                        target: 'upload_iframe_' + elementName,
                        method: 'post',
                        enctype: 'multipart/form-data',
                        class: 'ignore-validate',
                        action: uploadUrl
                    }).appendTo(this.wrapper);

                    this.inputFile = $('<input />', {
                        type: 'file',
                        name: 'datafile',
                        class: 'swatch_option_file'
                    }).appendTo(this.form);

                    $('<input />', {
                        type: 'hidden',
                        name: 'form_key',
                        value: FORM_KEY
                    }).appendTo(this.form);
                }
            };

            /**
             * Create swatch components
             */
            swatchComponents.create();

            /**
             * Register event for swatch input[type=file] change
             */
            swatchComponents.inputFile.change(function () {
                var localContainer = $('.' + $(this).attr('data-called-by')).parents().eq(2).children('.swatch_window'),

                    /**
                     * @this {iframe}
                     */
                    iframeHandler = function () {
                        var imageParams = $.parseJSON($(this).contents().find('body').html()),
                            fullMediaUrl = imageParams['swatch_path'] + imageParams['file_path'];

                        localContainer.parent().prev('input').val(imageParams['file_path']).trigger('change');
                        localContainer.css({
                            'background-image': 'url(' + fullMediaUrl + ')',
                            'background-size': 'cover'
                        });
                        localContainer.parent().removeClass('unavailable');
                    };

                swatchComponents.iframe.off('load');
                swatchComponents.iframe.load(iframeHandler);
                swatchComponents.form.submit();
                $(this).val('');
            });

            /**
             * Register event for choose "upload image" option
             */
            $(container).on('click', '.btn_choose_file_upload', function () {
                swatchComponents.inputFile.attr('data-called-by', $(this).data('class'));
                swatchComponents.inputFile.click();
            });

            /**
             * Register event for remove option
             */
            $(container).on('click', '.btn_remove_swatch', function () {
                var optionPanel = $(this).parents().eq(2);

                optionPanel.prev('input').val('').trigger('change');
                optionPanel.children('.swatch_window').css('background', '');
                optionPanel.addClass('unavailable');
                jQuery('.swatch_sub-menu_container').hide();
            });

            /**
             * Toggle color upload chooser
             */
            $(container).on('click', '.swatch_window', function () {
                jQuery('.swatch_sub-menu_container').hide();
                $(this).next('div').toggle();
            });
        });
    }

    return Abstract.extend({
        defaults: {
            elementId: 0,
            prefixName: '',
            prefixElementName: '',
            elementName: '',
            value: '',
            uploadUrl: ''
        },

        /**
         * Parses options and merges the result with instance
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();

            this.configureDataScope();

            return this;
        },

        /**
         * Initialize.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super()
                .initOldCode();

            return this;
        },
        
        getInitialValue: function () {
            var values = [this.value(), this.default],
                value;

            values.some(function (v) {
                if ((v !== "") && (v !== null) && (v !== undefined)) {
                    value = v;
                    return true;
                }
                return false;
            });
            return this.normalizeData(value);
        },

        /**
         * Initialize wrapped former implementation.
         *
         * @returns {Object} Chainable.
         */
        initOldCode: function () {
            jQuery.async('.' + this.elementName, function (elem) {
                oldCode(this.value(), elem.parentElement, this.uploadUrl, this.elementName, this.mediaUrl);
            }.bind(this));

            return this;
        },

        /**
         * Configure data scope.
         */
        configureDataScope: function () {
            var recordId, prefixName;

            // Get recordId
            recordId = this.parentName.split('.').last();

            prefixName = this.dataScopeToHtmlArray(this.prefixName);
            this.dataScope = 'data.' + this.prefixElementName + '.' + this.inputName;  
            this.elementName = this.prefixElementName + '_' + this.inputName;
            this.inputName = this.prefixElementName + '[' + this.inputName + ']';
            

            this.links.value = this.provider + ':' + this.dataScope;
        },

        /**
         * Get HTML array from data scope.
         *
         * @param {String} dataScopeString
         * @returns {String}
         */
        dataScopeToHtmlArray: function (dataScopeString) {
            var dataScopeArray, dataScope, reduceFunction;

            /**
             * Add new level of nesting.
             *
             * @param {String} prev
             * @param {String} curr
             * @returns {String}
             */
            reduceFunction = function (prev, curr) {
                return prev + '[' + curr + ']';
            };

            dataScopeArray = dataScopeString.split('.');

            dataScope = dataScopeArray.shift();
            dataScope += dataScopeArray.reduce(reduceFunction, '');

            return dataScope;
        }
    });
});
