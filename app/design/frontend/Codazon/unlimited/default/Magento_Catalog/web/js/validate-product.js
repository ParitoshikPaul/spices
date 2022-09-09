/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/mage',
    'Magento_Catalog/product/view/validation',
    'catalogAddToCart'
], function ($) {
    'use strict';

    $.widget('mage.productValidate', {
        options: {
            bindSubmit: false,
            radioCheckboxClosest: '.nested'
        },

        /**
         * Uses Magento's validation widget for the form object.
         * @private
         */
        _create: function () {
            var bindSubmit = this.options.bindSubmit;

            this.element.validation({
                radioCheckboxClosest: this.options.radioCheckboxClosest,

                /**
                 * Uses catalogAddToCart widget as submit handler.
                 * @param {Object} form
                 * @returns {Boolean}
                 */
                submitHandler: function (form) {
                    var jqForm = $(form).catalogAddToCart({
                        bindSubmit: bindSubmit
                    });

                    jqForm.catalogAddToCart('submitForm', jqForm);

                    return false;
                }
            });
            
            this.element.on('invalid-form.validate', function(event, validation) {
                var firstActive = $(validation.errorList[0].element || []);
                if (firstActive.length) {
                    var timeout = 100;
                    setTimeout(function() {
                        if (window.innerWidth < 768) {
                            var dy = 150;
                        } else {
                            var dy = 120;
                        }
                        var offsetTop = firstActive.offset().top - dy;
                        $('html, body').stop().animate({
                            scrollTop: offsetTop
                        });
                        firstActive.focus();
                    }, timeout);
                }
            });
        }
    });

    return $.mage.productValidate;
});
