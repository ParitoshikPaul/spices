/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require(['jquery', 'owlslider'], function($, owlSlider) {
if (typeof window.windowLoaded == 'undefined') window.windowLoaded = false;
!function(t,e){"use strict";function r(r,a,i,u,l){function f(){L=t.devicePixelRatio>1,i=c(i),a.delay>=0&&setTimeout(function(){s(!0)},a.delay),(a.delay<0||a.combined)&&(u.e=v(a.throttle,function(t){"resize"===t.type&&(w=B=-1),s(t.all)}),u.a=function(t){t=c(t),i.push.apply(i,t)},u.g=function(){return i=n(i).filter(function(){return!n(this).data(a.loadedName)})},u.f=function(t){for(var e=0;e<t.length;e++){var r=i.filter(function(){return this===t[e]});r.length&&s(!1,r)}},s(),n(a.appendScroll).on("scroll."+l+" resize."+l,u.e))}function c(t){var i=a.defaultImage,o=a.placeholder,u=a.imageBase,l=a.srcsetAttribute,f=a.loaderAttribute,c=a._f||{};t=n(t).filter(function(){var t=n(this),r=m(this);return!t.data(a.handledName)&&(t.attr(a.attribute)||t.attr(l)||t.attr(f)||c[r]!==e)}).data("plugin_"+a.name,r);for(var s=0,d=t.length;s<d;s++){var A=n(t[s]),g=m(t[s]),h=A.attr(a.imageBaseAttribute)||u;g===N&&h&&A.attr(l)&&A.attr(l,b(A.attr(l),h)),c[g]===e||A.attr(f)||A.attr(f,c[g]),g===N&&i&&!A.attr(E)?A.attr(E,i):g===N||!o||A.css(O)&&"none"!==A.css(O)||A.css(O,"url('"+o+"')")}return t}function s(t,e){if(!i.length)return void(a.autoDestroy&&r.destroy());for(var o=e||i,u=!1,l=a.imageBase||"",f=a.srcsetAttribute,c=a.handledName,s=0;s<o.length;s++)if(t||e||A(o[s])){var g=n(o[s]),h=m(o[s]),b=g.attr(a.attribute),v=g.attr(a.imageBaseAttribute)||l,p=g.attr(a.loaderAttribute);g.data(c)||a.visibleOnly&&!g.is(":visible")||!((b||g.attr(f))&&(h===N&&(v+b!==g.attr(E)||g.attr(f)!==g.attr(F))||h!==N&&v+b!==g.css(O))||p)||(u=!0,g.data(c,!0),d(g,h,v,p))}u&&(i=n(i).filter(function(){return!n(this).data(c)}))}function d(t,e,r,i){++z;var o=function(){y("onError",t),p(),o=n.noop};y("beforeLoad",t);var u=a.attribute,l=a.srcsetAttribute,f=a.sizesAttribute,c=a.retinaAttribute,s=a.removeAttribute,d=a.loadedName,A=t.attr(c);if(i){var g=function(){s&&t.removeAttr(a.loaderAttribute),t.data(d,!0),y(T,t),setTimeout(p,1),g=n.noop};t.off(I).one(I,o).one(D,g),y(i,t,function(e){e?(t.off(D),g()):(t.off(I),o())})||t.trigger(I)}else{var h=n(new Image);h.one(I,o).one(D,function(){t.hide(),e===N?t.attr(C,h.attr(C)).attr(F,h.attr(F)).attr(E,h.attr(E)):t.css(O,"url('"+h.attr(E)+"')"),t[a.effect](a.effectTime),s&&(t.removeAttr(u+" "+l+" "+c+" "+a.imageBaseAttribute),f!==C&&t.removeAttr(f)),t.data(d,!0),y(T,t),h.remove(),p()});var m=(L&&A?A:t.attr(u))||"";h.attr(C,t.attr(f)).attr(F,t.attr(l)).attr(E,m?r+m:null),h.complete&&h.trigger(D)}}function A(t){var e=t.getBoundingClientRect(),r=a.scrollDirection,n=a.threshold,i=h()+n>e.top&&-n<e.bottom,o=g()+n>e.left&&-n<e.right;return"vertical"===r?i:"horizontal"===r?o:i&&o}function g(){return w>=0?w:w=n(t).width()}function h(){return B>=0?B:B=n(t).height()}function m(t){return t.tagName.toLowerCase()}function b(t,e){if(e){var r=t.split(",");t="";for(var a=0,n=r.length;a<n;a++)t+=e+r[a].trim()+(a!==n-1?",":"")}return t}function v(t,e){var n,i=0;return function(o,u){function l(){i=+new Date,e.call(r,o)}var f=+new Date-i;n&&clearTimeout(n),f>t||!a.enableThrottle||u?l():n=setTimeout(l,t-f)}}function p(){--z,i.length||z||y("onFinishedAll")}function y(t,e,n){return!!(t=a[t])&&(t.apply(r,[].slice.call(arguments,1)),!0)}var z=0,w=-1,B=-1,L=!1,T="afterLoad",D="load",I="error",N="img",E="src",F="srcset",C="sizes",O="background-image";"event"===a.bind||o?f():n(t).on(D+"."+l,f)}function a(a,o){var u=this,l=n.extend({},u.config,o),f={},c=l.name+"-"+ ++i;return u.config=function(t,r){return r===e?l[t]:(l[t]=r,u)},u.addItems=function(t){return f.a&&f.a("string"===n.type(t)?n(t):t),u},u.getItems=function(){return f.g?f.g():{}},u.update=function(t){return f.e&&f.e({},!t),u},u.force=function(t){return f.f&&f.f("string"===n.type(t)?n(t):t),u},u.loadAll=function(){return f.e&&f.e({all:!0},!0),u},u.destroy=function(){return n(l.appendScroll).off("."+c,f.e),n(t).off("."+c),f={},e},r(u,l,a,f,c),l.chainable?a:u}var n=t.jQuery||t.Zepto,i=0,o=!1;n.fn.Lazy=n.fn.lazy=function(t){return new a(this,t)},n.Lazy=n.lazy=function(t,r,i){if(n.isFunction(r)&&(i=r,r=[]),n.isFunction(i)){t=n.isArray(t)?t:[t],r=n.isArray(r)?r:[r];for(var o=a.prototype.config,u=o._f||(o._f={}),l=0,f=t.length;l<f;l++)(o[t[l]]===e||n.isFunction(o[t[l]]))&&(o[t[l]]=i);for(var c=0,s=r.length;c<s;c++)u[r[c]]=t[0]}},a.prototype.config={name:"lazy",chainable:!0,autoDestroy:!0,bind:"load",threshold:500,visibleOnly:!1,appendScroll:t,scrollDirection:"both",imageBase:null,defaultImage:"data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==",placeholder:null,delay:-1,combined:!1,attribute:"data-src",srcsetAttribute:"data-srcset",sizesAttribute:"data-sizes",retinaAttribute:"data-retina",loaderAttribute:"data-loader",imageBaseAttribute:"data-imagebase",removeAttribute:!0,handledName:"handled",loadedName:"loaded",effect:"show",effectTime:0,enableThrottle:!0,throttle:250,beforeLoad:e,afterLoad:e,onError:e,onFinishedAll:e};if(windowLoaded){o=!0;}else{n(t).on("load",function(){o=!0})}}(window);

    $('body').on('contentUpdated', function () {
        require(['mage/apply/main'], function(mage) {
            if (mage) mage.apply();
        });
    });
    /* Common value */
    var mBreakpoint = 768,
    $win = $(window), winwidth = window.innerWidth, deskPrefix = 'desk_', mobiPrefix = 'mobi_', disHovImg = $('body').hasClass('product-disable-hover-img'),
    deskEvent = 'cdz_desktop', mobiEvent = 'cdz_mobile', winWidthChangedEvent = 'cdz_window_width_changed';
    
    /* jQuery functions */
    $.fn.searchToggle = function(options) {
        var defaultConf = {
            toggleBtn: '[data-role=search_toggle]',
            searchForm: '[data-role=search_form]',
            toggleClass: 'input-opened',
            mbClass: 'mb-search'
        };
        var conf = $.extend({}, defaultConf, options);
        return this.each(function() {
            var $element = $(this),
            $searchForm = $(conf.searchForm, $element),
            $searchBtn = $(conf.toggleBtn, $element);
            var mbSearch = function() {
                $element.addClass(conf.mbClass);
                $searchForm.removeClass('hidden-xs');
            };
            var dtSearch = function() {
                $element.removeClass(conf.mbClass);
                $searchForm.addClass('hidden-xs');
            };
            themecore.isMbScreen() ? mbSearch() : dtSearch();
            $win.on(deskEvent, dtSearch).on(mobiEvent, mbSearch);
            $searchBtn.on('click', function() {
                $element.toggleClass(conf.toggleClass);
            });
        });
    }
    /* Common functions */
    window.themecore = function() {
        return this;
    }; var thc = themecore;
    thc.stickyMenu = function() {
        require(['themewidgets'], function() {$.codazon.stickyMenu({}, $('.js-sticky-menu'));});
    };
    thc.backToTop = function() {
        if ($('#back-top').length == 0) $('<div id="back-top" class="back-top" data-role="back_top"><a title="Top" href="#top">Top</a></div>').appendTo('body');
        $('[data-role="back_top"]').each(function() {
            var $bt = $(this);
            $bt.on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({'scrollTop':0},800);
            });
            function toggleButton(hide) {
                hide ? $bt.fadeOut(300) : $bt.fadeIn(300);
            }
            var hide = ($win.scrollTop() < 100);
            toggleButton(hide);
            $win.on('scroll', function() {
                var newState = ($win.scrollTop() < 100);
                if(newState != hide){
                    hide = newState;
                    toggleButton(hide);
                }
            });
        });
    }
    thc.b64DecodeUnicode = function(str) {
        return decodeURIComponent(Array.prototype.map.call(atob(str), function (c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    };
    thc.isMbScreen = function(breakpoint) {
        if (typeof breakpoint === 'undefined') breakpoint = mBreakpoint;
        return (window.innerWidth < breakpoint);
    };
    thc.isDtScreen = function(breakpoint) {
        if (typeof breakpoint === 'undefined') breakpoint = mBreakpoint;
        return (window.innerWidth >= breakpoint);
    };
    thc.uniqid = function(prefix) {
        return (prefix ? prefix : '') + Math.random().toString().substring(2,8);
    };
    thc.triggerAdaptScreen = function(breakpoint) {
        var self = this;
        if (typeof breakpoint === 'undefined') breakpoint = mBreakpoint;
        var eventSuffix =  (breakpoint == mBreakpoint)? '' : '_' + breakpoint, winwidth = window.innerWidth,
        triggerMedia = function() {
            self.isMbScreen(breakpoint) ? $win.trigger(mobiEvent + eventSuffix) : $win.trigger(deskEvent + eventSuffix);
        }, checkAdpatChange = function() {
            var curwidth = window.innerWidth;
            if ( ((winwidth < breakpoint) && (curwidth >= breakpoint) ) || 
               ( (winwidth >= breakpoint) && (curwidth < breakpoint)) ) {
                $win.trigger('adaptchange' + eventSuffix);
                triggerMedia();
            }
            winwidth = curwidth;
        }, t = false;
        $win.on('resize', function() {
            if(t) clearTimeout(t);
            t = setTimeout(checkAdpatChange, 50);
        });
        triggerMedia();
    };
    thc.autoTrigger = function() {
        $('body').on('click', '[data-autotrigger]', function(e) {
            e.preventDefault();
            var $trigger = $(this), $triggerTarget = $($trigger.data('autotrigger')).first();
            $triggerTarget.trigger('click');
        });
    }
    thc.moveElToNewContainer = function(fromPrefix, toPrefix) {
        $('[id^="' + fromPrefix + '"]').each(function() {
            var $element = $(this),
            $children = $element.children(),
            fromId = $element.attr('id'),
            toId = toPrefix + fromId.substr(fromPrefix.length);
            $children.appendTo('#' +toId);
        });
    };
    thc.moveFromSourceElement = function() {
        $('[data-movefrom]').each(function() {
            var $dest = $(this), $source = $($dest.data('movefrom')).first();
            $dest.replaceWith($source);
        });
    };
    thc.setupMobile = function() {
        thc.moveElToNewContainer(deskPrefix, mobiPrefix);
    };
    thc.setupDesktop = function() {
        thc.moveElToNewContainer(mobiPrefix, deskPrefix);
    };
    thc.qtyControl = function() {
        $('body').off('click.cdzQtyControl').on('click.cdzQtyControl','[data-role=change_cart_qty]', function (e) {
            var $btn = $(this);
            if ($btn.data('role') != 'change_cart_qty') {
                $btn = $btn.parents('[data-role=change_cart_qty]').first();
            }
            var qty = $btn.data('qty'), $pr = $btn.parents('.cart-qty').first(),
            $qtyInput = $('input.qty',$pr),
            curQty = $qtyInput.val()?parseInt($qtyInput.val()):0;
            curQty += qty;
            (curQty < 1) ? (curQty = 1) : null;
            $qtyInput.val(curQty).attr('value', curQty).trigger('change');
        });
    }
    thc.winWidthChangedEvent = function() {
        var curwidth = window.innerWidth;
        $win.on('resize', function() {
            if (window.innerWidth != curwidth) {
                curwidth = window.innerWidth;
                $win.trigger(winWidthChangedEvent, [curwidth]);
            }
        });
    };
    thc.scrollTo = function() {
        $('body').on('click', '[data-scollto]', function(e) {
            e.preventDefault();
            var $button = $(this), $dest = $($button.data('scollto'));
            if ($dest.is(':visible')) {
                $('html, body').animate({scrollTop: $dest.offset().top - 100}, 300);
            } else {
                if ($dest.parents('[role=tabpanel]').length) {
                    $('a.switch[href="#' + $dest.parents('[role=tabpanel]').first().attr('id') + '"]').click();
                    setTimeout(function() {
                        $('html, body').animate({scrollTop: $dest.offset().top - 100}, 300);
                    }, 300);
                }
            }
        });
    };
    thc.updateFormKey = function() {
        $('.product-item form [name="form_key"]').val($('input[name="form_key"]').first().val());
    }
    thc.cdzLazyImage = function() {
        $('[data-lazysrc]').each(function() {
            var $img = $(this).attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAoMBgDTD2qgAAAAASUVORK5CYII=');
            if ($img.attr('width') && $img.attr('height') && (!$img.parent().hasClass('abs-img'))) {
                $img.wrap($('<span class="abs-img">').css({paddingBottom: (100*$img.attr('height'))/$img.attr('width') + '%'}));
            }
            $img.addClass('cdz-lazy owl-lazy').attr('data-src', $img.data('lazysrc')).removeAttr('data-lazysrc').Lazy({afterLoad: function() {
                $img.removeClass('cdz-lazy owl-lazy').css('display', '');
            }});
        });
    }
    thc.init = function() {
        var self = this;
        this.triggerAdaptScreen();
        this.triggerAdaptScreen(1200);
        this.winWidthChangedEvent();
        var sht, lt = false;
        $win.on(deskEvent, this.setupDesktop)
            .on(mobiEvent, this.setupMobile)
            .on(winWidthChangedEvent, function() {
                if (sht) clearTimeout(sht);
                sht = setTimeout(self.makeSameHeight, 300);
            });
        self.cdzLazyImage();
        $('body').on('contentUpdated cdzResize', function() {
            if (sht) clearTimeout(sht);
            sht = setTimeout(self.makeSameHeight, 100);
        }).on('cdzTabsOpened', function(e, $tab) {
            if (sht) clearTimeout(sht);
            sht = setTimeout(function() {
                self.makeSameHeight($tab);
            }, 100);
        }).on('swatch.initialized', function(e) {
            self.makeSameHeight($(e.target).parents('[data-sameheight]').first().parent());
        });
        $(document).ajaxComplete(function() {
            if (lt) clearTimeout(lt);
            lt = setTimeout(self.cdzLazyImage, 500);
        });
        this.isMbScreen() ? this.setupMobile() : this.setupDesktop();        
		this.toggleMobileMenu();
        this.autoTrigger();
        this.qtyControl();
        this.sectionMenu();
        this.remoteSliderNav();
        this.scrollTo();
        this.mobiProductViewTabs();
        $(document).ready(function() {
            self.mbtoolbar();
            self.toggleContent();
            self.moveFromSourceElement();
            self.attachCustomerInfo();
            self.verticalMenu();
            self.backToTop();
            if (self.isMbScreen()) {
                self.setupMobile();
            } else {
                self.setupDesktop();
            }
        });
        $('body').on('contentUpdated', function() {
            self.toggleContent();
            self.cdzLazyImage();
        });
        self.makeSameHeight();
        var onLoaded = function () {
            self.ajaxHandler();
            self.stickyMenu();
            self.makeSameHeight();
            self.updateFormKey();
            self.qtyControl();
        }
        if (window.windowLoaded) {
            onLoaded();
        } else {
            $win.on('load', onLoaded);
        }
    };
    thc.ajaxHandler = function() {
        $(document).ajaxStart(function() {
            $('body').addClass('cdz-ajax-loading');
        });
        $(document).ajaxStop(function() {
            $('body').removeClass('cdz-ajax-loading');
        });
    };
    thc.remoteSliderNav = function() {
        $('body').on('click', '[data-targetslider]', function() {
            var $btn = $(this), sliderId = $btn.data('targetslider'),
            $slider = $('#' + sliderId).find('.owl-carousel');
            if ($slider.length) {
                if ($btn.hasClass('owl-prev')) {
                    $slider.trigger('prev.owl.carousel');
                } else {
                    $slider.trigger('next.owl.carousel');
                }
            }
        });
    };
    thc.attachCustomerInfo = function() {
        function loadCustomerInfo() {
            if ($('[data-customerinfo]').length) {
                $.ajax({
                    url: codazon.customerDataUrl,
                    type: "get",
                    cache: false,
                    success: function(data) {
                        if (data.customer) {
                            var customer = data.customer;
                            if (customer) {
                                $('[data-customerinfo]').each(function() {
                                    var $info = $(this), info = $info.data('customerinfo');
                                    $info.removeAttr('data-customerinfo');
                                    if (customer[info]) {
                                        $info.replaceWith(customer[info].replace(/(<([^>]+)>)/ig,''));
                                    }
                                });
                            }
                        }
                    }
                });
            }
        }
        loadCustomerInfo();
        $('body').on('contentUpdated', loadCustomerInfo);
    };
    thc.mbtoolbar = function() {
        var $toolbar = $('#mb-bottom-toolbar');
        var $btnSlider = $('[data-role=group-slider]', $toolbar);
        var $switcher = $('[data-role=switch-group]');
        var clicked = false;
        $btnSlider.owlCarousel({
            items: 1,
            dots: false,
            nav: false,
            animateIn: 'changing',
            animateOut: false,
            touchDrag: false,
            mouseDrag: false,
            rtl: $('body').hasClass('rtl-layout'),
            onChanged: function(property) {
                if (clicked) {
                    var dotsCount = $switcher.find('.dot').length;
                    $switcher.toggleClass('return');
                    $switcher.find('.dot').each(function(i, el){
                        var $dot = $(this);
                        setTimeout(function() {
                            $dot.removeClass('wave-line').addClass('wave-line');
                            setTimeout(function() {
                                $dot.removeClass('wave-line');
                            }, 1000);
                        }, i*100);
                    });
                    setTimeout(function() {
                        $btnSlider.find('.owl-item').removeClass('changing animated');
                    },300);
                    clicked = false;
                }
            }
        });
        var owl = $btnSlider.data('owl.carousel'), slideTo = 0;
        $switcher.on('click', function(e) {
            clicked = true;
            e.preventDefault();
            slideTo = !slideTo;
            owl.to(slideTo, 1, true);
        });
        var $currentDisplay = false, $currentPlaceholder = $('<div class="mb-toolbar-placeholder">').hide().appendTo('body'),
        $toolbarContent = $toolbar.find('[data-role=mb-toolbar-content]').first(), eventType = (typeof window.orientation == 'undefined') ? 'click' : 'touchend';
        $toolbar.find('[data-action]').on(eventType, function(e) {
            e.preventDefault();
            var $btn = $(this);
            var action = $btn.data('action');
            if (action.display) {
                if (!$toolbar.hasClass('content-opened')) {
                    $toolbar.addClass('content-opened');
                    if (action.display.element) {
                        if ($(action.display.element).length) {
                            $currentDisplay = $(action.display.element).first();
                            $currentPlaceholder.insertBefore($currentDisplay);
                            $currentDisplay.appendTo($toolbarContent);
                        }
                    }
                } else {
                    $toolbar.removeClass('content-opened')
                    if ($currentDisplay) {
                        $currentDisplay.insertAfter($currentPlaceholder);
                        $currentDisplay = false;
                    }
                }
            }
            if (action.trigger) {
                $(action.trigger.target).trigger(action.trigger.event);
            }
        });
        $toolbar.on('click', '[data-role=close-content]', function() {
            $toolbar.removeClass('content-opened');
            if ($currentDisplay) {
                $currentDisplay.insertAfter($currentPlaceholder);
                $currentDisplay = false;
            }
        });
    };
    thc.makeSameHeight = function($context) {
        if (typeof $context == 'undefined') $context = $('body');
        $('[data-sameheight]', $context).each(function() {
            var $element = $(this);
            if ($element.is(':visible')) {
                var sameHeightArray = $element.data('sameheight').split(',');
                $.each(sameHeightArray, function(i, sameHeight) {
                    var maxHeight = 0;
                    $element.find(sameHeight).css({minHeight: ''}).each(function() {
                        var $sItem = $(this), height = $sItem.outerHeight();
                        if (height > maxHeight) {
                            maxHeight = height;
                        }
                    }).css({minHeight: maxHeight});
                });
            }
        });
    };
    thc.sectionMenu = function() {
        if ($('[data-secmenuitem]').length) {
            var processing = false, topSpace = 100, $wrap = $('<div class="section-menu-wrap hidden-xs">'), $menu = $('<div class="section-menu">');
            $menu.appendTo($wrap.appendTo('body'));
            var sections = [];
            $('[data-secmenuitem]').each(function() {
                var $section = $(this), $menuItem = $('<div class="menu-item">'), data = $section.data('secmenuitem'), icon = data.icon, title = data.title;
                $menuItem.html('<i class="' + icon + '"></i>');
                if (title) $menuItem.append('<div class="item-label"><span>' + title + '</span></div>');
                $menuItem.appendTo($menu).on('click', function() {
                    if (!processing) {
                        var sectionTop = $section.offset().top - topSpace;
                        $menuItem.addClass('active').siblings().removeClass('active');
                        processing = true;
                        $('html, body').animate({scrollTop: sectionTop}, 300, 'linear', function() {
                            setTimeout(function() {
                                processing = false;
                            },100);
                        });
                    }
                });
                $section.removeAttr('data-secmenuitem');
                sections.push({
                    menuItem: $menuItem,
                    section: $section
                });
            });
            
            var title = 'Back to Top';
            var $home = $('<div class="menu-item go-top"><i class="sec-icon fa fa-arrow-circle-up"></i></div>')
                .append('<div class="item-label"><span>' + title + '</span></div>')
                .prependTo($menu).on('click', function() {
                $('html, body').animate({scrollTop: 0});
            });
            if ($win.scrollTop() > window.innerHeight - topSpace) {
                $wrap.addClass('open');
            } else {
                $wrap.removeClass('open');
            }
            $win.on('scroll', function() {
                if (thc.isDtScreen() && !processing) {
                    $.each(sections, function(id, item) {                        
                        var elTop = item.section.offset().top - topSpace,
                        elBot = elTop + item.section.outerHeight(),
                        winTop = $win.scrollTop(),
                        winBot = winTop + window.innerHeight;
                        if (winTop > window.innerHeight - topSpace) {
                            $wrap.addClass('open');
                        } else {
                            $wrap.removeClass('open');
                        }
                        var cond1 = (elTop <= winTop) && (elBot >= winTop),
                        cond2 = (elTop >= winTop) && (elTop <= winBot),
                        cond3 = (elTop >= winTop) && (elBot <= winBot),
                        cond4 = (elTop <= winTop) && (elBot >= winBot);
                        if (cond1 || cond2 || cond3 || cond4) {
                            item.menuItem.addClass('active').siblings().removeClass('active');
                            return false;
                        }
                    });
                }
            });
        }
    }
    thc.checkVisible = function($element){
        var cond1 = ($element.get(0).offsetWidth > 0) && ($element.get(0).offsetHeight > 0), cond2 = ($element.is(':visible')), winTop = $win.scrollTop(),
        winBot = winTop + window.innerHeight,
        elTop = $element.offset().top,
        elHeight = $element.outerHeight(true),
        elBot = elTop + elHeight;
        var cond3 = (elTop <= winTop) && (elBot >= winTop),
        cond4 = (elTop >= winTop) && (elTop <= winBot),
        cond5 = (elTop >= winTop) && (elBot <= winBot),
        cond6 = (elTop <= winTop) && (elBot >= winBot),
        cond7 = true;
        if ($element.parents('md-tab-content').length > 0) {
            cond7 = $element.parents('md-tab-content').first().hasClass('md-active');
        }
        return cond1 && cond2 && (cond3 || cond4 || cond5 || cond6) && cond7;
    };
    thc.toggleContent = function() {
        var self = this;
        $('[data-cdz-toggle]').each(function() {            
            var $link = $(this).addClass('link-toggle'),
            $content = $($link.data('cdz-toggle'));
            if ($content.length) {
                $content.attr('data-role', 'cdz-toggle-content');
                $link.removeAttr('data-cdz-toggle').on('click', function() {
                    if (self.isMbScreen()) {
                        $content.toggleClass('active');
                        if ($content.hasClass('active')) {
                            $link.addClass('active');
                        } else {
                            $link.removeClass('active');
                        }
                        $content.slideToggle(300);
                    }
                });
                $win.on(deskEvent, function() {
                    $link.removeClass('active');
                });
            }
        });
        $('[data-role=cdz-toggle-content]').each(function() {
            var $content = $(this);
            if (self.isMbScreen()) {
                $content.hide();
            }
            $win.on(deskEvent, function() {
                $content.css({display: ''}).removeClass('active');
            }).on(mobiEvent, function() {
                $content.css({display: 'none'}).removeClass('active');
            });
            $content.removeAttr('data-role');
        });
    };
    
    thc.mobiProductViewTabs = function() {
        if ($('body').hasClass('catalog-product-view')) {
            $('body').on('click', '.product.info.detailed a.data.switch', function() {
                if (window.innerWidth < mBreakpoint) {
                    var $tab = $(this);
                    setTimeout(function() {
                        if ($tab.offset().top < window.scrollY) $('html, body').animate({'scrollTop': ($tab.offset().top - 100)}, 300);
                    }, 150);
                }
            });
        }
    };
    thc.verticalMenu = function () {
        if (window.codazon.alignVerMenuHeight) {
            function alignMenu($menu) {
                var $menuwrap = $menu.parents('[data-role=menu-content]').first();
                if ($menuwrap.length && $menuwrap.parents('.column.main').length) {
                    var $slideshow = $('[data-role="cdz-slideshow"]').first(), height, t = false, eventName = winWidthChangedEvent + '.vmenu lazyLoadInitialized.vmenu';
                    cont = $menuwrap.data('align_container'), $cont = $(cont); $menu.addClass('aligned');
                    if ($cont.length == 0) {
                        $cont = $slideshow.parent();
                    }
                    if ($cont.length) {
                        var calcHeight = function() {
                            $menuwrap.removeClass('fixed-height-menu');
                            $menu.css('position', 'absolute');
                            height = $cont.outerHeight(false);
                            $menu.css('position', '');
                            var menuHeight = $menu.height();
                            if (height < menuHeight) {
                                $menuwrap.addClass('fixed-height-menu').css({height: height});
                            }
                        }
                        calcHeight();
                        $win.off(eventName).on(eventName, function(e) {
                            if (t) clearTimeout(t);
                            $menuwrap.removeClass('fixed-height-menu').css({height: ''});
                            t = setTimeout(calcHeight, 500);
                        });
                    }
                }
            }
            $('.column.main [data-role=menu-content] .cdz-menu').each(function() {
                alignMenu($(this));
            });
            $('body').on('cdzmenu.initialized', function(e, $menu) {
                alignMenu($menu);
            });
        }
    };
    thc.toggleMobileMenu = function() {
		$('[data-role=menu-title]').each(function() {
			var $title = $(this),
			$menu = $title.parent().find('[data-role=menu-content]').removeClass('hidden-xs'),
            onMobile = function() {
				$menu.hide();
			}, onDesktop = function() {
				$menu.css({display: ''});
			}, toggle = function() {
                (window.innerWidth < mBreakpoint) ? onMobile() : onDesktop();
			}
			$title.on('click', function() {
				if (window.innerWidth < mBreakpoint) $menu.slideToggle(200);
			});
			$win.on(mobiEvent, onMobile).on(deskEvent, onDesktop);
			toggle();
		});
	}
    thc.init();
    /* Handler */
});