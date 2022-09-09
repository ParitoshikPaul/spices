(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            "jquery",
            "jquery-ui-modules/widget",
            "catalogAddToCart"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";
    $.widget('codazon.ajaxInfiniteLoad', {
        options: {
            trigger: '[data-role=ajax_trigger]',
            itemsWrap: '.product-items',
            ajaxLoader: '[data-role=ajax_loader]',
            currentUrl: '',
            formKeyInputSelector: 'input[name="form_key"]'
        },
        _currentPage: 1,
        _create: function(){
            var self = this;
            self.element.find(self.options.trigger).click(function(){
                self._ajaxLoadProducts();
            });
        },
        _ajaxLoadProducts: function(){
            var self = this, config = this.options, $trigger = self.element.find(config.trigger),
            $ajaxLoader = self.element.find(config.ajaxLoader), hasLastPage = false,
            startOffset = self.element.find('.product-item').length;
            $trigger.hide();
            $ajaxLoader.show();
            self._currentPage++;
            config.jsonData.cur_page = self._currentPage;
            config.jsonData.current_url = config.currentUrl;
            jQuery.ajax({
                url: config.ajaxUrl,
                type: "GET",
                data: config.jsonData,
                cache: true,
                success: function(res){
                    if (typeof res.now !== 'undefined') {
                        window.codazon.now = res.now;
                    }
                    if(res.html) {
                        self.formKey = $(config.formKeyInputSelector).first().val();
                        $(config.itemsWrap, self.element).append(res.html);
                        setTimeout(function() {
                            self.element.find('[name="form_key"]').each(function() {
                                var $field = $(this).val(self.formKey);
                            });
                        }, 500);
                        if (typeof window.angularCompileElement !== 'undefined') {
                            window.angularCompileElement(self.element);
                        }
                    }
                    if(res.last_page <= self._currentPage){
                        hasLastPage = true;
                    }
                    require(['mage/apply/main'], function(mage) {
                        if (mage) mage.apply();
                        $('body').trigger('contentUpdated');
                        setTimeout(function() {
                            $('body').trigger('ajaxProductInfiniteLoaded');
                        }, 100);
                    });
                },
                error: function(XMLHttpRequest, textStatus, errorThrown){
                    self._currentPage--;
                    console.error(textStatus);
                }
            }).always(function(){
                $ajaxLoader.hide();
                if(!hasLastPage) {
                    $trigger.show();
                } else {
                    $trigger.hide();
                }
            });
        }
    });
    return $.codazon.ajaxInfiniteLoad;
}));
