/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */ 
(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            "jquery",
            "jquery-ui-modules/widget",
            "Magento_Ui/js/modal/modal"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($,ui,modal) {
    "use strict";
    $.widget('codazon.quickview', {
        options: {
            modalId: 'quickshop',
        },
        _create: function(){
            this._buildQuickShop(this.options);
         },
        _buildQuickShop: function(config){
            var self = this, $modal = $('#' + config.modalId);
            if ($modal.length == 0) {
                $modal = $('<div id="' + config.modalId + '" class="quickshop-modal"><div class="content-wrap"><div class="qs-content qs-main"></div></div></div>');
                $modal.appendTo('body');
                window.qsModal = $modal.modal({
                    innerScroll: true,
                    wrapperClass: 'qs-modal',
                    buttons: [],
                    opened: function(){
                        var $content = $modal.find('.qs-content');
                        $content.hide();
                        $.ajax({
                            url: window.qsUrl,
                            type: 'GET',
                            cache: true,
                            showLoader: true,
                            success: function(res){
                                $('body').addClass('cdz-qs-view');
                                res = res.replace(/Magento_Swatches\/js\/swatch-renderer/g, 'Codazon_QuickShop/js/swatch-renderer');
                                res = res.replace(/"configurable": {/g, '"Codazon_QuickShop/js/configurable": {');
                                res = res.replace(/"configurable":{/g, '"Codazon_QuickShop/js/configurable":{');
                                res = res.replace(/#review-form/g, '#reviews');
                                res = res.replace(/\[data-gallery-role=gallery-placeholder\]/g, '.quickshop-index-view [data-gallery-role=gallery-placeholder]');
                                res = res.replace(/"#product_addtocart_form"/g, '".quickshop-index-view #product_addtocart_form"');
                                $content.html(res);
                                if (typeof window.angularCompileElement != 'undefined') {
                                    window.angularCompileElement($content);
                                }
                                $content.trigger('contentUpdated');
                                $content.trigger('cdzBuildPopup');
                                $content.show();
                                var formKey = $('[name="form_key"]').first().val();
                                $content.find('form [name="form_key"]').val(formKey);
                                if ($content.find('#bundle-slide').length > 0) {
                                    var $bundleBtn = $content.find('#bundle-slide');
                                    var $bundleTabLink = $('#tab-label-quickshop-product-bundle-title');
                                    setTimeout(function(){
                                        $bundleBtn.off('click').click(function(e){
                                            e.preventDefault();
                                            $bundleTabLink.parent().show();
                                            $bundleTabLink.click();
                                            return false;
                                        });
                                    },500);
                                }
                            }
                        });
                    },
                    closed: function(){
                        $('[data-gallery-role="gallery"]', $modal).first().data('fotorama').destroy();
                        $modal.find('.qs-content').html('');
                        $('body').removeClass('cdz-qs-view');
                        if ($('body').hasClass('temp-catalog-product-view')) {
                            $('body').removeClass('temp-catalog-product-view catalog-product-view');
                        }
                    }
                });
            }
            self.element.on('click', function(e){
                var $handler = $(this);
                window.qsUrl = config.qsUrl;
                window.qsModal.modal('openModal');
            });
        }
    });
    return $.codazon.quickview;
}));
