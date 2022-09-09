/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate',
    'jquery-ui-modules/widget',
    'Magento_Ui/js/modal/modal'
], function($, $t, widget, modal) {
    "use strict";
    return function (widget) {
        $.widget('mage.catalogAddToCart', widget, {
            _create: function() {
                this._super();
            },
            ajaxSubmit: function(form) {
                var that = this;
                form.trigger('addToCartBegin');
                this.oldAction = form.attr('action');
                var action = this.oldAction.replace('checkout/cart', 'shoppingcart/cart/add');
                if (form.parents('.product-item').length) {
                    var hasOptions = form.parents('.product-item').first().find('[data-hasoptions]').first().data('hasoptions');
                    if (hasOptions == '1') {
                        this.getProductOptions(form, action);
                        return false;
                    }
                }
                if (action.search('options=cart') > -1) {                
                    this.getProductOptions(form, action);
                    return false;
                }
                
                window.ajaxShoppingCart.latestAddedProductId = form.find('[name=product]').val();
                
                if (window.ajaxShoppingCart.miniCartStyle == 2) {
                    this._flyingCart(form, 'footer');
                }
                if (window.ajaxShoppingCart.miniCartStyle == 3) {
                    this._flyingCart(form, 'sidebar');
                }
                if (window.ajaxShoppingCart.miniCartStyle == 2 || window.ajaxShoppingCart.miniCartStyle == 3) {
                     var _super = this._super.bind(this);
                    setTimeout(function() {
                        _super(form);
                    }, 1000);
                } else {
                    this._super(form);
                }
            },
            disableAddToCartButton: function(form) {
                if (!form.attr('buy_now')) {
                    this._super(form);
                }
            },
            getProductOptions: function(form) {
                var self = this, conf = this.options;
                
                var $modal = $('#product-options-modal');
                
                if (typeof window.ajaxcartModal === 'undefined') {
                    var $modal = $('<div id="product-options-modal" class="quickshop-modal"><div class="content-wrap"><div class="qs-content qs-main"></div></div></div>');
                    $modal.appendTo('body');
                    window.ajaxcartModal = $modal; modal({
                        innerScroll: true,
                        buttons: [],
                        wrapperClass: 'qs-modal product-options-modal',
                        closed: function() {
                            $('[data-gallery-role="gallery"]', $modal).first().data('fotorama').destroy();
                            $modal.find('.qs-content').html('');
                            $('body').removeClass('cdz-qs-view');
                        }
                    }, $modal);
                }
                
                var $content = $modal.find('.qs-content').first();
                $.ajax({
                    url: window.ajaxShoppingCart.optionUrl,
                    data: {id: form.find('[name=product]').val()},
                    type: 'get',
                    showLoader: true,
                    success: function(res) {
                        $('body').addClass('cdz-qs-view');
                        res = res.replace(/Magento_Swatches\/js\/swatch-renderer/g, 'Codazon_QuickShop/js/swatch-renderer');
                        res = res.replace(/"configurable": {/g, '"Codazon_QuickShop/js/configurable": {');
                        res = res.replace(/#review-form/g, '#reviews');
                        res = res.replace(/\[data-gallery-role=gallery-placeholder\]/g, '.quickshop-index-view [data-gallery-role=gallery-placeholder]');
                        res = res.replace(/"#product_addtocart_form"/g, '".quickshop-index-view #product_addtocart_form"');
                        $content.html(res);
                        var formKey = $('[name="form_key"]').first().val();
                        $content.find('form [name="form_key"]').val(formKey);
                        if (typeof window.angularCompileElement != 'undefined') {
                            window.angularCompileElement($content);
                        }
                        $content.trigger('contentUpdated');
                        $content.show();
                        window.ajaxcartModal.modal('openModal');
                        
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
                                $bundleBtn.click();
                            },500);
                        }
                    }
                });
                
            },
            enableAddToCartButton: function(form) {
                var self = this;
                
                if (!form.attr('buy_now')) {
                    this._super(form);
                    form.trigger('addToCartCompleted');
                    if (this.oldAction) {
                        form.attr('action', this.oldAction);
                    }
                } else {
                    form.trigger('addToCartCompleted');
                    if (this.oldAction) {
                        form.attr('action', this.oldAction);
                    }
                    return false;
                }
                if (form.parents('#quickshop').length) {
                    $('#quickshop').modal('closeModal');
                    if (window.ajaxShoppingCart.miniCartStyle == 1) {
                        setTimeout(function() {
                            self.showInformedPopup(form);
                        }, 200);
                    }
                } else if (form.parents('.product-options-modal').length) {
                    window.ajaxcartModal.modal('closeModal');
                    if (window.ajaxShoppingCart.miniCartStyle == 1) {
                        setTimeout(function() {
                            self.showInformedPopup(form);
                        }, 200);
                    }
                } else {
                    if (window.ajaxShoppingCart.miniCartStyle == 1) {
                        this.showInformedPopup(form);
                    }
                }
            },
            showInformedPopup: function(form) {
                var self = this, config = this.options,
                popupId = window.ajaxShoppingCart.popupId,
                $popup = $('#' + popupId);
                if ($popup.length) {
                    if ($('.cart-informed-modal').length == 0) {
                        modal({
                            innerScroll: true,
                            buttons: [],
                            wrapperClass: 'cart-informed-modal',
                            opened: function() {
                                $('body').addClass('cart-informed-modal-opened');
                                $('[data-block=\'minicartpro\']').trigger('dropdowndialogopen');
                                $('.cart-informed-modal .modal-content').addClass('nice-scroll');  
                            },
                            closed: function() {
                                $('body').removeClass('cart-informed-modal-opened');
                            }
                        }, $popup); 
                    }
                    $popup.trigger('cartLoading');
                    if (!$('body').hasClass('cart-informed-modal-opened')) {
                        $popup.modal('openModal');
                    } else {
                        $('[data-block=\'minicartpro\']').trigger('dropdowndialogopen');
                    }
                }
            },
            _flyingCart: function(form, type) {
                var $container = $('[data-block=minicartpro]'),  $img, $effImg, $parent, src, $destination, $panelContent;
                $container.trigger('cartLoading');
                if ((window.innerWidth < 768) &&  ($('.js-footer-cart a').length)) {
                    $destination = $('.js-footer-cart a').first();
                } else {
                    if (type == 'footer') {
                        $destination = $('[data-block=minicartpro] [data-role=flying-destination]').first();
                    } else {
                        $destination = $('#desk_cart-wrapper');
                    }
                }
                $panelContent = $('[data-block=minicartpro] .block-minicartpro');
                if (form.parents('.product-item').length) {
                    $parent = form.parents('.product-item').first();
                    $img = $parent.find('.product-item-photo img').first();
                } else {
                    $img = $('.fotorama__active img.fotorama__img');
                }
                if ($img.length) {
                    $effImg = $('<img style="display: none; position:absolute; z-index:100000"/>');
                    $('body').append($effImg);
                    src = $img.attr('src');
                    var width = $img.width(), height = $img.height();
                    var step01Css = {
                        top: (($img.offset().top > $(window).scrollTop()) ? $img.offset().top : ($(window).scrollTop() + 10)),
                        left: $img.offset().left,
                        width: width,
                        height: height
                    }
                    $effImg.attr('src', src).css(step01Css);
                    var flyImage = function () {
                        $effImg.show();
                        var newWidth = 0.1*width, newHeight = 0.1*height;
                        var step02Css = {
                            top: $destination.offset().top,
                            left: $destination.offset().left,
                            width: newWidth,
                            height: newHeight
                        }
                        $effImg.animate(step02Css, 1000, 'linear', function () {
                            $effImg.fadeOut(100, 'swing', function () {
                                $effImg.remove();
                                if (type == 'sidebar') {
                                    $container.addClass('opened');
                                }
                            });
                        });
                    }
                    if (type == 'footer') {
                        if ( !$panelContent.is('*:visible') ) {
                            $panelContent.css({minHeight:'none'}).slideDown(300, 'swing', flyImage);
                        } else {
                            flyImage();
                        }
                    } else {
                        flyImage();
                    }
                }
            }
        });
    };
});