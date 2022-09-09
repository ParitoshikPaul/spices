/**
 * Copyright © 2020 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Catalog/js/product/list/listing'
], function ($, Listing) {
    'use strict';
    return Listing.extend({
        initialize: function () {
            this._super();
        },
        getListHtml: function(obj) {
            var ids = this._getIds(), self = this, uid = 'block-' + Math.random().toString().substr(2,6);
            if (ids.length) {
                var jsonData = this.jsonData;
                jsonData.current_url = this.currentUrl;
                delete jsonData.cache_key_info;
                delete jsonData.conditions_encoded;
                jsonData.display_type = 'all_products';
                jsonData.product_ids = ids.join(',');
                jsonData.cache_lifetime = -1;
                $.ajax({
                    url: self.ajaxUrl,
                    type: "POST",
                    data: jsonData,
                    cache: false,
                    success: function(res) {
                        if (typeof res.now !== 'undefined') {
                            window.codazon.now = res.now;
                        }
                        var $element = $('#' + uid);
                        if (typeof res.html !== 'undefined') {
                            var formKey = $('input[name="form_key"]').first().val();
                            $element.html(res.html).removeClass('no-loaded');
                            setTimeout(function() {
                                $element.find('[name="form_key"]').each(function() {
                                    $(this).val(formKey);
                                });
                            }, 500);
                            if (typeof window.angularCompileElement !== 'undefined') {
                                window.angularCompileElement($element);
                            }
                        }
                        require(['mage/apply/main'], function(mage) {
                            if (mage) {
                                mage.apply();
                            }
                            $('body').trigger('contentUpdated');
                            setTimeout(function() {
                                $('body').trigger('ajaxProductFirstTimeLoaded');
                            }, 100);
                        });
                    }
                })
            } else {
                return '<div class="message info"><div>' + this.noResultMsg + '</div></div>';
            }
            return '<div id="' + uid + '"></div>'
        },
        _getIds: function() {
            var ids = [];
            $.each(this.filteredRows(), function(i, row) {
                ids.push(row.id);
            });
            return ids;
        }
    });
});