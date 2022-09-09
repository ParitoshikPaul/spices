/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar',
    'mage/translate',
    'mage/dropdown'
], function (Component, customerData, $, ko, _) {
    'use strict';
    
    /* Custom mage.sidebar features */
    $.widget('mage.sidebar', $.mage.sidebar, {
        _updateItemQty: function (elem) {
            var itemId = elem.data('cart-item');
            var prefix = this.options.prefix ? this.options.prefix : '';
            this._ajax(this.options.url.update, {
                'item_id': itemId,
                'item_qty': $('#' + prefix + 'cart-item-' + itemId + '-qty').val()
            }, elem, this._updateItemQtyAfter);
        },
        _showItemButton: function (elem) {
            var itemId = elem.data('cart-item'),
                itemQty = elem.data('item-qty');
            
            var prefix = this.options.prefix ? this.options.prefix : '';
            
            if (this._isValidQty(itemQty, elem.val())) {
                $('#' + prefix + 'update-cart-item-' + itemId).show('fade', 300);
            } else if (elem.val() == 0) {
                this._hideItemButton(elem);
            } else {
                this._hideItemButton(elem);
            }
        },
        _removeItem: function (elem) {
            elem.parents('[data-role="product-item"]').first().addClass('disabling');
            this._super(elem);
        }
    });

    var sidebarInitialized = false,
        addToCartCalls = 0,
        miniCart;

    miniCart = $('[data-block=\'minicartpro\']');

    /**
     * @return {Boolean}
     */
    
    function prepeareFooterCart() {
        if ($('#cdz-footer-minicart').length) {
            var $footerCart = $('#cdz-footer-minicart');
            if (!$footerCart.data('prepared')) {
                $footerCart.css('display', '');
                var $cartTrigger, $cartContent, $itemTrigger;
                $cartContent = $('[data-role=cart-content]', $footerCart).first();
                $footerCart.on('click', '[data-role=cart-trigger]', function() {
                    $cartContent.slideToggle(300, 'linear', function() {
                        $footerCart.toggleClass('opened');
                    });
                });
                $footerCart.on('click', '[data-role=item-trigger]', function() {
                    var $trigger = $(this), $item = $trigger.parents('.product-item').first(), $actions = $('[data-role=item-actions]', $item).first();
                    $item.toggleClass('active').siblings().removeClass('active');
                });
                $footerCart.data('prepared', true);
            }
            $footerCart.on('cartLoading', function() {
                $footerCart.find('[data-role=cart-count]').hide();
                $footerCart.find('[data-role=cart-processing]').show();
            });
            $footerCart.on('cartLoaded', function() {
                $footerCart.find('[data-role=cart-count]').show();
                $footerCart.find('[data-role=cart-processing]').hide();
            });
        }
    };
    
    function prepareInformedPopup() {
        if ($('#cdz-minicart-popup').length) {
            var $popupCart = $('#cdz-minicart-popup');
            if (!$popupCart.data('prepared')) {
                $popupCart.on('cartLoading', function() {
                    $popupCart.addClass('ajaxcart-loading');
                });
                $popupCart.on('cartLoaded', function() {
                    $popupCart.removeClass('ajaxcart-loading');
                });
                $popupCart.data('prepared', true);
            }
            if (!$popupCart.find('.minicart-items-wrapper .section-content-inner').hasClass('nice-scroll')) {
                $popupCart.find('.minicart-items-wrapper .section-content-inner').addClass('nice-scroll');
            }
            $popupCart.on('click', '[data-action=close]', function() {
                $popupCart.modal('closeModal');
            });
        }
    }
    
    function prepeareSidebarCart() {
        if ($('#cdz-sidebar-minicart').length) {
            var $sidebarCart = $('#cdz-sidebar-minicart');
            if (!$sidebarCart.data('prepared')) {
                var $cartTrigger, $cartContent, $itemTrigger;
                $sidebarCart.on('click', '[data-role=cart-trigger]', function() {
                    $sidebarCart.toggleClass('opened');
                    if ($sidebarCart.hasClass('opened')) {
                        $sidebarCart.trigger('dropdowndialogopen');
                    }
                });
                $sidebarCart.on('click', '[data-action=close]', function() {
                    $sidebarCart.removeClass('opened');
                });
                $sidebarCart.on('cartLoading', function() {
                    $sidebarCart.addClass('ajaxcart-loading');
                });
                $sidebarCart.on('cartLoaded', function() {
                    $sidebarCart.removeClass('ajaxcart-loading');
                });
                $sidebarCart.data('prepared', true);
            }
        }
    }
    
    function initSidebar() {
        if (miniCart.data('mageSidebar')) {
            miniCart.sidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            miniCart.trigger('cartLoaded');
            return false;
        }
        miniCart.trigger('contentUpdated');

        if (sidebarInitialized) {
            miniCart.trigger('cartLoaded');
            return false;
        }
        sidebarInitialized = true;
        
        miniCart.sidebar({
            'targetElement': '.block-minicartpro',
            'prefix': 'cdz-',
            'url': {
                'checkout': window.checkout.checkoutUrl,
                'update': window.checkout.updateItemQtyUrl,
                'remove': window.checkout.removeItemUrl,
                'loginUrl': window.checkout.customerLoginUrl,
                'isRedirectRequired': window.checkout.isRedirectRequired
            },
            'button': {
                'checkout': '#minicartpro-btn-checkout,#sidebar-cart-btn-checkout',
                'remove': '#minicartpro-content-wrapper a.action.delete',
                'close': '#minicartpro-content-wrapper .btn-minicart-close'
            },
            'showcart': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'minicart': {
                'list': '#minicartpro-content-wrapper [data-role=cart-items-list]',
                'content': '#minicartpro-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.ajaxShoppingCart.minicartMaxItemsVisible
            },
            'item': {
                'qty': ':input.cart-item-qty',
                'button': ':button.update-cart-item'
            },
            'confirmMessage': $.mage.__('Are you sure you would like to remove this item from the shopping cart?')
        });
        miniCart.find('[data-action="closeInformedPopup"]').off('click').on('click', function() {
            miniCart.modal('closeModal');
        });
        
        prepareInformedPopup();
        prepeareFooterCart();
        prepeareSidebarCart();
        miniCart.trigger('cartLoaded');
    }
    
    prepareInformedPopup();
    prepeareFooterCart();
    prepeareSidebarCart();
    
    miniCart.on('dropdowndialogopen', function () {
        initSidebar();
    });

    return Component.extend({
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        maxItemsToDisplay: 100,//window.checkout.maxItemsToDisplay,
        cart: {},
        crosssell: {},

        /**
         * @override
         */
        initialize: function () {
            var self = this,
                cartData = customerData.get('cart'),
                crosssellData = customerData.get('crosssell');

            this.update(cartData());
            this.updateCrosssell(crosssellData());
            
            cartData.subscribe(function (updatedCart) {
                addToCartCalls--;
                this.isLoading(addToCartCalls > 0);
                miniCart.trigger('cartLoading');
                sidebarInitialized = false;
                this.update(updatedCart);
                initSidebar();
            }, this);
            crosssellData.subscribe(function(crosssell) {
                this.updateCrosssell(crosssell);
                initSidebar();
            }, this);
            
            $('[data-block="minicartpro"]').on('contentLoading', function () {
                addToCartCalls++;
                self.isLoading(true);
            });

            if (cartData()['website_id'] !== window.checkout.websiteId) {
                customerData.reload(['cart'], false);
            }
            window.cdzShoppingCart = this;
            return this._super();
        },
        isLoading: ko.observable(false),
        initSidebar: initSidebar,

        /**
         * Close mini shopping cart.
         */
        closeMinicart: function () {
            $('[data-block="minicartpro"]').find('[data-role="dropdownDialog"]').dropdownDialog('close');
        },

        /**
         * @return {Boolean}
         */
        closeSidebar: function () {
            var minicart = $('[data-block="minicartpro"]');

            minicart.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                minicart.find('[data-role="dropdownDialog"]').dropdownDialog('close');
            });
            return true;
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini shopping cart content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedCart) {
            _.each(updatedCart, function (value, key) {
                if (!this.cart.hasOwnProperty(key)) {
                    this.cart[key] = ko.observable();
                }
                this.cart[key](value);
            }, this);
        },
        
        updateCrosssell: function (crosssell) {
            _.each(crosssell, function (value, key) {
                if (!this.crosssell.hasOwnProperty(key)) {
                    this.crosssell[key] = ko.observable();
                }
                this.crosssell[key](value);
            }, this);
        },
        /**
         * Get cart param by name.
         * @param {String} name
         * @returns {*}
         */
        getCartParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.cart.hasOwnProperty(name)) {
                    this.cart[name] = ko.observable();
                }
            }

            return this.cart[name]();
        },
        
        getCrosssellParam: function(name) {
             if (!_.isUndefined(name)) {
                if (!this.crosssell.hasOwnProperty(name)) {
                    this.crosssell[name] = ko.observable();
                }
            }
            return this.crosssell[name]();
        },

        /**
         * Returns array of cart items, limited by 'maxItemsToDisplay' setting
         * @returns []
         */
        getCartItems: function () {
            var items = this.getCartParam('items') || [];

            //items = items.slice(parseInt(-this.maxItemsToDisplay, 10));

            return items;
        },
        
        getNewestItem: function () {
            var items = this.getCartParam('items') || [];
            var newestItem = items[0];
            
            //if (window.ajaxShoppingCart.latestAddedProductId) {
                var products = this.getCrosssellParam('items') || [];
                var checkedProduct = products; /* = []*/
                /* $.each(products, function(id, product) {
                    if (window.ajaxShoppingCart.latestAddedProductId == product.product_id) {
                        product.item_id = id;
                        checkedProduct.push(product);
                    }
                }); */
                if (checkedProduct.length) {
                    var neededProduct = checkedProduct[0],
                    createdAt = Date.parse(neededProduct.created_at),
                    updateddAt = (Date.parse(neededProduct.updated_at).toString() === 'NaN') ? 0 : Date.parse(neededProduct.updated_at);
                    $.each(checkedProduct, function(id, product) {
                        var newCreatedAt = Date.parse(product.created_at),
                        newUpdatedAt = (Date.parse(product.updated_at).toString() === 'NaN') ? 0 : Date.parse(product.updated_at),
                        newDate = (newUpdatedAt > 0) ? newUpdatedAt :  newCreatedAt,
                        oldDate = (updateddAt > 0) ? updateddAt :  createdAt;
                        
                        if (newDate > oldDate) {
                            neededProduct = product;
                            createdAt = newCreatedAt;
                            updateddAt = newUpdatedAt;
                        }
                    });
                    $.each(items, function(id, item) {
                        if (neededProduct.item_id == item.item_id) {
                            newestItem = item;
                            return false;
                        }
                    });
                }
            //}
            return newestItem;
        },

        /**
         * Returns count of cart line items
         * @returns {Number}
         */
        getCartLineItemsCount: function () {
            var items = this.getCartParam('items') || [];

            return parseInt(items.length, 10);
        }
    });
});
