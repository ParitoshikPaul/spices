/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'jquery-ui-modules/widget',
    'Magento_Swatches/js/swatch-renderer'
], function ($, _) {
    'use strict';
    $.widget('codazon.SwatchRenderer', $.mage.SwatchRenderer, {
        _create: function() {
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.qs-modal'),
                isProductViewExist = true,
                $main = this.element.parents('.qs-modal');

            gallery.data('gallery') ?
                    this._onGalleryLoaded(gallery) :
                    gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));

            this.productForm = this.element.parents(this.options.selectorProductTile).find('form:first');
            this.inProductList = this.productForm.length > 0;
        },
        _loadMedia: function () {
            var $main = this.element.parents('.qs-modal'),
                images;

            if (this.options.useAjax) {
                this._debouncedLoadProductMedia();
            }  else {
                images = this.options.jsonConfig.images[this.getProduct()];

                if (!images) {
                    images = this.options.mediaGalleryInitial;
                }
                this.updateBaseImage(this._sortImages(images), $main, !this.inProductList);
            }
        },
        _determineProductData: function () {
            return {
                productId: $('.qs-modal [name=product]').val(),
                isInProductView: true
            };
        },
        
        _EnableProductMediaLoader: function ($this) {
            var $widget = this;
            $this.parents('.qs-modal').find('.photo.image')
                    .addClass($widget.options.classes.loader);
        },
        
        _DisableProductMediaLoader: function ($this) {
            var $widget = this;
            $this.parents('.qs-modal').find('.photo.image')
                    .removeClass($widget.options.classes.loader);
        },
        _ProductMediaCallback: function ($this, response) {
            
            var isInProductView = true;
            
            var $main = $this.parents('.qs-modal'),
                $widget = this,
                images = [],

                /**
                 * Check whether object supported or not
                 *
                 * @param {Object} e
                 * @returns {*|Boolean}
                 */
                support = function (e) {
                    return e.hasOwnProperty('large') && e.hasOwnProperty('medium') && e.hasOwnProperty('small');
                };

            if (_.size($widget) < 1 || !support(response)) {
                this.updateBaseImage(this.options.mediaGalleryInitial, $main, isInProductView);

                return;
            }

            images.push({
                full: response.large,
                img: response.medium,
                thumb: response.small,
                isMain: true
            });

            if (response.hasOwnProperty('gallery')) {
                $.each(response.gallery, function () {
                    if (!support(this) || response.large === this.large) {
                        return;
                    }
                    images.push({
                        full: this.large,
                        img: this.medium,
                        thumb: this.small
                    });
                });
            }
            this.updateBaseImage(images, $main, isInProductView);
        },
        
        _LoadProductMedia: function() {
            var $widget = this,
                $this = $widget.element,
                attributes = {},
                productId = 0,
                mediaCallData,
                mediaCacheKey,

                /**
                 * Processes product media data
                 *
                 * @param {Object} data
                 * @returns void
                 */
                mediaSuccessCallback = function (data) {
                    if (!(mediaCacheKey in $widget.options.mediaCache)) {
                        $widget.options.mediaCache[mediaCacheKey] = data;
                    }
                    $widget._ProductMediaCallback($this, data);
                    $widget._DisableProductMediaLoader($this);
                };

            if (!$widget.options.mediaCallback) {
                return;
            }

            $this.find('[option-selected]').each(function () {
                var $selected = $(this);

                attributes[$selected.attr('attribute-code')] = $selected.attr('option-selected');
            });

            productId = $('.qs-modal [name=product]').val();

            mediaCallData = {
                'product_id': productId,
                'attributes': attributes,
                'additional': $.parseQuery()
            };
            mediaCacheKey = JSON.stringify(mediaCallData);

            if (mediaCacheKey in $widget.options.mediaCache) {
                mediaSuccessCallback($widget.options.mediaCache[mediaCacheKey]);
            } else {
                mediaCallData.isAjax = true;
                $widget._XhrKiller();
                $widget._EnableProductMediaLoader($this);
                $widget.xhr = $.post(
                    $widget.options.mediaCallback,
                    mediaCallData,
                    mediaSuccessCallback,
                    'json'
                ).done(function () {
                    $widget._XhrKiller();
                });
            }
        },
        processUpdateBaseImage: function (images, context, isInProductView, gallery) {
            if (!gallery) {
                gallery = $('[data-gallery-role=gallery-placeholder]', '.qs-modal').data('gallery');
            }
            this._super(images, context, isInProductView, gallery);
        }
    });

    return $.codazon.SwatchRenderer;
});
