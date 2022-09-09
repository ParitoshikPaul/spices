/**
 * Copyright © 2021 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery'], function($) {
    var $win = $(window);
    $.widget('codazon.scrollMenu', {
        options: {
            atClass: 'scrm-active',
        },
        _create: function() {
            this._prepareHtml();
        },
        _prepareHtml: function () {
            var self = this, conf = this.options, $wg = this.element, t = false;
            this.$orgList = self.element.find('.groupmenu').first();
            this.$inWrap = $('<div class="menu-inner">').appendTo($wg);
            this.$topList = $('<ul class="groupmenu m-top-list">').appendTo(this.$inWrap);
            this.$orgList.addClass('m-org-list').appendTo(this.$inWrap);
            this.$orgList.find('> li.level0').each(function() {
                var $orgli = $(this);
                var $topLi = $('<li>').addClass($orgli.attr('class')).appendTo(self.$topList);
                var $a = $orgli.find('> .menu-link').clone().appendTo($topLi);
                $topLi.hover(function() {
                    if (t) clearTimeout(t);
                    t = setTimeout(function() {
                        self._alignMenu();
                        $topLi.addClass(conf.atClass).siblings().removeClass(conf.atClass);
                        $orgli.addClass(conf.atClass).siblings().removeClass(conf.atClass).trigger('mouseover.lazyimages');
                    }, 10);
                }, function() {});
                $a.on('click', function(e) {
                    if (conf.menuWidget._isTabletDevice()) {
                        if ($topLi.hasClass('parent')) {
                            e.stopPropagation();
                            e.preventDefault();
                        }
                    }
                });
            });
            this.$inWrap.on('mouseleave', function() {
                $('.'+conf.atClass, self.$inWrap).removeClass(conf.atClass);
            });
            this.$ctn = $wg.parents('[data-role="menu-container"]').first();
            this.$ctnTitle = null;
            if (this.$ctn.length) {
                this.$ctn.addClass('has-scroll-menu');
                this.$ctnTitle = $('[data-role="menu-title"]', this.$ctn).first();
            }
            var alignMenu = function () {
                self.$inWrap.css({'margin-left': '', 'margin-right':''}).removeClass('js-aligned');
                if (self.$inWrap.is(':visible')) {
                    self._alignMenu();
                } else {
                    if (self.$ctnTitle) {
                        self.$ctnTitle.one('click', function() {
                            setTimeout(function() {
                                self._alignMenu();
                            }, 300);
                        });
                    }
                }
            }
            alignMenu();
            $win.on('cdz_window_width_changed changeHeaderState', function() {
                alignMenu();
            });
            if (self.$ctnTitle && self.$ctnTitle.hasClass('closebyaround')) {
                this.$orgList.on('click', function(e) {
                    var $tg = $(e.target);
                    if (!($tg.hasClass(conf.atClass) || $tg.parents('.'+conf.atClass).length)) {
                        self.$ctnTitle.trigger('click');
                    }
                })
            }
        },
        _alignMenu: function() {
            if (!this.$inWrap.hasClass('js-aligned')) {
                this.$inWrap.addClass('js-aligned');
                var winW = $win.outerWidth();
                if (winW > 767) {
                    var self = this, conf = this.options, $wg = this.element;
                    var wrW = this.$inWrap.outerWidth(), wrLeft = $wg.offset().left, wrRight = wrW + wrLeft,
                        d = winW - wrRight;
                    if (this._isRTL()) {
                        if (wrLeft < 0) {
                            this.$inWrap.css('margin-right', wrLeft - 20);
                        }
                    } else {
                        if (d < 0) {
                            this.$inWrap.css('margin-left', d - 20);
                        }
                    }
                }
            }
        },
        _isRTL: function() {
            return $('body').hasClass('rtl-layout');
        }
    });
    return $.codazon.scrollMenu;
});