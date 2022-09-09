/**
 * Copyright © 2019 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'priceUtils',
    'priceBox',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'Magento_ConfigurableProduct/js/configurable'
], function ($, _, mageTemplate, $t, priceUtils) {
    'use strict';
    $.widget('codazon.customConfigurable', $.mage.configurable, {
        _create: function() {
            var wrap = '.quickshop-modal ';
            this.options.superSelector = wrap + this.options.superSelector;
            this.options.selectSimpleProduct = wrap + this.options.selectSimpleProduct;
            this.options.priceHolderSelector = wrap + this.options.priceHolderSelector;
            this.options.normalPriceLabelSelector = wrap + this.options.normalPriceLabelSelector;
            this.options.tierPriceTemplateSelector = wrap + this.options.tierPriceTemplateSelector;
            this.options.tierPriceBlockSelector = wrap + this.options.tierPriceBlockSelector;
            this.options.slyOldPriceSelector = wrap + this.options.slyOldPriceSelector;
            this.options.mediaGallerySelector = wrap + this.options.mediaGallerySelector;
            this._super();
        }
    });
    return $.codazon.customConfigurable;
});