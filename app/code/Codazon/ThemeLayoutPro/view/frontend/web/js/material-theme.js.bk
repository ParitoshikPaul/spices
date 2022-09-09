window.angularCompileElement = function() {
    if (typeof window.componentHandler !== 'undefined') {
        window.componentHandler.upgradeAllRegistered(self.element);
    }
}
require(['jquery'], function($) {
    "use strict";
    if (typeof window.cdzUtilities == 'undefined') {
        window.cdzUtilities = {};
    }
    var deskPrefix = 'desk_', mobiPrefix = 'mobi_';
    var deskEvent = 'cdz_desktop', mobiEvent = 'cdz_mobile';
    var $window = $(window);
    var rtl = $('body').hasClass('rtl-layout');
    var mBreakpoint = 768;
    var winWidthChangedEvent = 'cdz_window_width_changed';
    var $body = $('body');
    
    $('body').on('materialUpdated', window.angularCompileElement);
    
    cdzUtilities.getScrollBarWidth = function() {
        var $outer = $('<div>').css({visibility: 'hidden', width: 100, overflow: 'scroll'}).appendTo('body'),
            widthWithScroll = $('<div>').css({width: '100%'}).appendTo($outer).outerWidth();
        $outer.remove();
        return 100 - widthWithScroll;
    };
    cdzUtilities.tooltips = function() {
        $('.js-cdz-tooltip').each(function() {
            var $button = $(this);
            $button.removeClass('js-cdz-tooltip');
            if ($button.attr('title')) {
                var id = $button.attr('id') ? $button.attr('id') : 'cdz-tooltip-' + Math.round((10000*Math.random()));
                $button.attr('id', id);
                var $tooltip = $('<div class="mdl-tooltip mdl-tooltip--top">').text($button.attr('title')).attr('for', id).appendTo($button);
            }
        });
        window.angularCompileElement();
    };
    
    cdzUtilities.sidebar = function() {
        var $backface = $('#cdz-sidebar-backface');
        
        if ($backface.length == 0) {
            $backface = $('<div data-role="cdz-close-sidebar" id="cdz-sidebar-backface" class="cdz-sidebar-backface" >');
            $backface.appendTo('body');
        }
        var side, $sidebar, section, openedEvent, interval;
        function closeSidebar() {
            $('html').removeClass('cdz-panel-open-left cdz-panel-open-right');
            $('html').addClass('cdz-panel-close-' + side);
            openedEvent = false;
            if (interval) clearInterval(interval);
            setTimeout(function() {
                $sidebar.css('top', '');
                $('html').removeClass('cdz-panel-close-' + side);
                $('#' + section).hide();
                $body.css({paddingLeft: '', paddingRight: ''});
            }, 200);
        }
        function openSidebar() {
            $sidebar.css('top', $(window).scrollTop());
            if (interval) clearInterval(interval);
            interval = setInterval(function() {
                $sidebar.css('top', $(window).scrollTop());
            }, 100);
            $('html').removeClass('cdz-panel-open-left cdz-panel-open-right')
                    .addClass('cdz-panel-open-' + side);
            $('#' + section).show().siblings().hide();
            (side == 'right')?$body.css({paddingLeft: cdzUtilities.scrollBarWidth}):$body.css({paddingRight: cdzUtilities.scrollBarWidth});
            setTimeout(function() {
                if (openedEvent) {
                    $('#' + section).trigger(openedEvent);
                }
            },300);
        }
        
        $('body').on('click', '[data-sidebartrigger]', function(e) {
            e.preventDefault();
            var $trigger = $(this);
            var data = $trigger.data('sidebartrigger');
            
            section = data.section ? data.section : 'utilities-main';
            side = data.side ? data.side : 'right';
            $sidebar = $('[data-sidebarid=' + side + ']').first();
            openedEvent = data.event;
            
            if ($('html').hasClass('cdz-panel-open-' + side)) {
                closeSidebar();
            } else {
                openSidebar();
            }
            $sidebar.find('[data-action=close]').off('click').on('click', function() {
                closeSidebar();
            });
            
        });
        $('body').on('click touchend', '[data-role=cdz-close-sidebar]', function(e) {
            setTimeout(function() {
                closeSidebar();
            }, 50);
        });
    };
    
    cdzUtilities.dropdown = function() {
        var $container = false, $trigger, $dropdown, activeClass = 'cdz-dd-active', active = '.' + activeClass;
        $('body').on('click', '[data-role=cdz-dd-trigger]', function(e) {
            e.preventDefault();
            $trigger = $(this);
            $container = $trigger.parents('[data-role=cdz-dropdown]').first();
            $dropdown = $container.find('[data-role=cdz-dd-content]');
            
            if ($container.hasClass(activeClass)) {
                $container.removeClass(activeClass);
                $container = false;
            } else {
                $(active).removeClass(activeClass);
                $container.addClass(activeClass);
                var ddRight = $container.offset().left + $dropdown.outerWidth() + 20;
                var delta = 0;
                if (ddRight > window.innerWidth) {
                    delta = ddRight - window.innerWidth;
                }
                $dropdown.css({left: -delta});
                setTimeout(function() {
                    $dropdown.trigger('dropdowndialogopen');
                }, 300);
                
            }
        });
        $('body').on('click', function(e) {
            if ($container) {
                var $target = $(e.target);
                var cond1 = $target.is($container), cond2 = ($container.has($target).length > 0);

                if (!(cond1 || cond2)) {
                    $container.removeClass(activeClass);
                    $dropdown.css({left: ''});
                }
            }
        });
    };
    
    cdzUtilities.popup = function() {
        var $popupContainer, $ppContainerInner, $openedPopup, $backface;
        function _prepare() {
            $popupContainer = $('#cdz-popup-area');
            if ($popupContainer.length == 0) {
                $popupContainer = $('<div class="cdz-popup-area" id="cdz-popup-area">');
                $popupContainer.appendTo('body');
                $ppContainerInner = $('<div class="cdz-popup-area-inner" >').appendTo($popupContainer);
                $backface = $('<div class="cdz-backface" data-role="close-cdzpopup">').appendTo($ppContainerInner);
            }
        }
        function _buildPopup() {
            $('[data-cdzpopup]').each(function() {
                var $popup = $(this);
                var $wrap = $('<div class="cdz-popup">').appendTo($ppContainerInner);
                $wrap.addClass('popup-' + $popup.attr('id'));
                var $inner = $('<div class="cdz-popup-inner">').appendTo($wrap);
                var $content = $('<div class="cdz-popup-content">').appendTo($inner);
                var $closeBtn = $('<button type="button" class="close-cdzpopup" data-role="close-cdzpopup"><span></span></button>').appendTo($wrap);
                $popup.removeAttr('data-cdzpopup');
                $popup.appendTo($content);
                if (!$popup.hasClass('no-nice-scroll')) {
                    $content.addClass('nice-scroll');
                }
                if ($popup.hasClass('hidden-overflow')) {
                    $content.css({overflow: 'hidden'});
                }
                if ($popup.data('parentclass')) {
                    $wrap.addClass($popup.data('parentclass'));
                }
                $popup.on('triggerPopup', function() {
                    cdzUtilities.triggerPopup($popup.attr('id'));
                });
            });
        }
        this.triggerPopup = function(popupId, $trigger) {
            var $popup = $('#' + popupId);
            if ($popup.length) {
                if ($popup.parents('.cdz-popup').length) {
                    $popup.parents('.cdz-popup').first().addClass('opened').siblings().removeClass('opened');
                    $('body').css({overflow: 'hidden'});
                    $('.js-sticky-menu.active').css({
                        right: 'auto',
                        width: 'calc(100% - ' + cdzUtilities.scrollBarWidth +'px)'
                    });
                    $('body').addClass('cdz-popup-opened');
                    setTimeout(function() {
                        $popup.trigger('cdz_popup_opened');
                        if ($trigger) {
                            if (typeof $trigger.data('event') === 'string') {
                                $popup.trigger($trigger.data('event'));
                            }
                        }
                    }, 300);
                }
            }
        }
        function _bindEvents() {
            $('body').on('click', '[data-cdzpopuptrigger]', function(e) {
                e.preventDefault();
                var $trigger = $(this);
                var popupId = $trigger.data('cdzpopuptrigger');
                cdzUtilities.triggerPopup(popupId, $trigger);
            });
            function closePopup() {
                $('.cdz-popup.opened').removeClass('opened');
                $('body').removeClass('cdz-popup-opened');
                $('body').css({overflow: ''});
                $('.js-sticky-menu').css({right: '', width: ''});
            }
            function modifyButton($button, it) {
                $button.attr('id', 'btn-minicart-close-popup');
                if (!$button.data('popup_bind_event')) {
                    $button.data('popup_bind_event', true);
                    $button.on('click', closePopup);
                    $popupContainer.find('#top-cart-btn-checkout').on('click', closePopup);
                    if (it) clearInterval(it);
                }
            }
            if ($popupContainer.find('div.block.block-minicart').length) {
                var it = setInterval(function() {
                    var $button = $popupContainer.find('#btn-minicart-close');
                    if ($button.length) {
                        modifyButton($button, it);
                    }
                }, 2000);
                require(['Magento_Customer/js/customer-data'], function(customerData) {
                    var cartData = customerData.get('cart');
                    cartData.subscribe(function (updatedCart) {
                        var $button = $popupContainer.find('#btn-minicart-close');
                        if ($button.length) {
                            setTimeout(function() {
                                modifyButton($button, false);
                            }, 1000);
                        }
                    });
                });
            }
            $popupContainer.on('click', '[data-role=close-cdzpopup]', closePopup);
        }
        
        _prepare();
        _buildPopup();
        _bindEvents();
        $('body').on('cdzBuildPopup', _buildPopup);
        
    };
    function replaceTag($obj, newTag) {
        var $newTag = $('<' + newTag + '>');
        $newTag.insertAfter($obj);
        if($obj.children().length) {
            $obj.children().appendTo($newTag);
        } else if($obj.html()) {
            $newTag.html($obj.html());
        }
        var obj = $obj.get(0);
        $.each(obj.attributes, function(id, el) {
            $newTag.attr(el.name, el.value);
        });
        $obj.remove();
        return $newTag;
    }
    cdzUtilities.builDynamicTabs = function() {
        var deskEvent = 'cdz_desktop', mobiEvent = 'cdz_mobile';
        $window = $(window);
        $('[data-role=tabs-dynamic-control]').each(function(){
            var $tabs = $(this);
            var $container = $tabs.parents('.js-tab-dc-container').first();
            if ($container.length == 0) {
                $container = $tabs.parents('[data-role=js-tab-dc-container]');
            }
            var $linkPlaceholder = $container.find('.tab-links-placeholder');
            if ($linkPlaceholder.length == 0) {
                $linkPlaceholder = $container.find('[data-role=tab-links-placeholder]');
            }
            var $ul = $('<ul class="box-cate-link hidden-xs abs-dropdown">');
            $linkPlaceholder.empty();
            $ul.appendTo($linkPlaceholder).unwrap();
            var $mbTitle = $('<a href="javascript:void(0)" class="mobile-toggle visible-xs">');
            $mbTitle.insertBefore($ul);
            $('.tab-item', $tabs).each(function(id, el) {
                var $tabItem = $(this), external = false, href = 'javascript:void(0)';
                if ($tabItem.find('.tab-title').data('externalurl')) {
                    href = $tabItem.find('.tab-title').data('externalurl');
                    external = true;
                }
                var $tabTitle = replaceTag($tabItem.find('.tab-title'), 'a').attr('href', href).removeAttr('data-externalurl');
                var $li = $('<li class="item">').addClass($tabTitle.data('class')).append($tabTitle.removeAttr('data-class'));
                $li.appendTo($ul);
                if (id == 0) {
                    $li.addClass('active');
                    $tabItem.addClass('active');
                    $mbTitle.text($tabTitle.text());
                }
                $li.on('click', function() {
                    if (!external) {
                        $tabItem.addClass('active').siblings().removeClass('active');
                        $li.addClass('active').siblings().removeClass('active');
                        $mbTitle.text($tabTitle.text());
                        if (window.innerWidth < 768) {
                            $ul.slideUp(300);
                            $mbTitle.removeClass('open');
                        }
                    }
                });
            });
            $ul.removeClass('hidden-xs');
            function toggleUL() {
                if (window.innerWidth < 768) {
                    $ul.hide();
                } else {
                    $ul.css('display', '');
                }
            }
            toggleUL();
            $window.on(deskEvent, toggleUL).on(mobiEvent, toggleUL);
            $('body').on('click', function(e) {
                if ($mbTitle.hasClass('open')) {
                var $target = $(e.target);
                    var cond1 = $target.is($mbTitle),
                    cond2 = $mbTitle.find($target).length,
                    cond3 = $target.is($ul),
                    cond4 = $ul.find($target).length;
                    if (!(cond1 || cond2 || cond3 || cond4)) {
                        $ul.slideUp(300);
                        $mbTitle.removeClass('open');
                    }
                }
            });
            $mbTitle.on('click', function() {
                $ul.slideToggle(300);
                $mbTitle.toggleClass('open');
            });
            $tabs.removeClass('hidden');
        });
    };
    
    cdzUtilities.effectLabel = function() {
        var context = '.form-edit-account,.form-login,.form-create-account, form.contact, form.review-form, form.form.search.advanced, form.form-address-edit, form.form-newsletter-manage, form#blog_search_mini_form, [data-effectform]';
        var inputs = '.field input[type=text],.field input[type=email],.field input[type=number],.field input[type=password], .field textarea, .field select:not(.multiselect)';
        var checkboxs = '.field [type=checkbox],.field [type=checkbox]';
        var buttons = '[type=submit]';
        var radios = '.field [type=radio]';
        $(inputs, context).each(function() {
            var $input = $(this);
            if (!$input.hasClass('mdl-textfield__input')) {
                var $parent = $input.parents('.field').first();
                var $wrap = $('<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-upgraded">').insertBefore($input);
                $input.addClass('mdl-textfield__input').appendTo($wrap);
                if ($wrap.next().hasClass('ui-datepicker-trigger')) {
                    $wrap.next().appendTo($wrap);
                }
                if ($parent.hasClass('no-label')) {
                    $('<label>').addClass('mdl-textfield__label').appendTo($wrap);
                } else {
                    var $label = $parent.find('label');
                    $label.addClass('mdl-textfield__label').appendTo($wrap);
                    if ($input.attr('placeholder')) {
                        $input.removeAttr('placeholder');
                    }
                }
                if ($input.attr('name') == 'dob') {
                    $input.on('change', function() {
                        if ($input.val()) {
                            $wrap.addClass('is-dirty');
                        }
                    });
                }
            }
        });
        $(checkboxs, context).each(function() {
            var $checkbox = $(this);
            if (!$checkbox.hasClass('mdl-switch__input')) {
                var $parent = $checkbox.parents('.field').first();
                var $label = $parent.find('label');
                var label = $label.text();
                $label.empty();
                $label.append($('<span class="mdl-switch__label">').text(label));
                $label.addClass('mdl-switch mdl-js-switch mdl-js-ripple-effect').insertBefore($checkbox);
                $checkbox.addClass('mdl-switch__input').prependTo($label);
            }
        });
        $(radios, context).each(function() {
            var $radio = $(this);
            if (!$radio.hasClass('mdl-radio__button')) {
                if ($radio.parents('.review-field-ratings').length == 0) {
                    var $parent = $radio.parents('.field').first();
                    var $label = $parent.find('label');
                    var label = $label.text();
                    $label.empty();
                    $label.append($('<span class="mdl-radio__label">').text(label));
                    $label.addClass('mdl-radio mdl-js-radio mdl-js-ripple-effect').insertBefore($radio);
                    $radio.addClass('mdl-radio__button').prependTo($label);
                }
            }
        });
        // $(buttons, context).each(function() {
            // var $button = $(this);
            // if (!$button.hasClass('mdl-button')) {
                // $button.addClass('mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect');
            // }
        // });
        window.componentHandler ? window.componentHandler.upgradeAllRegistered() : false;
    };
    
    cdzUtilities.customCheckbox = function() {
        $('.block.related .block-actions [role="select-all-related"]').on('click', function(e) {
            setTimeout(function() {
                $('input.checkbox.related.mdl-checkbox__input').each(function() {
                    var $checkbox = $(this);
                    if ($checkbox.is(':checked')) {
                        $checkbox.parents('label.mdl-checkbox').first().addClass('is-checked');
                    } else {
                        $checkbox.parents('label.mdl-checkbox').first().removeClass('is-checked');
                    }
                });
            }, 100);
        });
    };
    
    cdzUtilities.init = function() {
        var self = this;
        this.scrollBarWidth = cdzUtilities.getScrollBarWidth();
        var winwidth = window.innerWidth;
        $(window).on('resize', function() {
            if (winwidth != window.innerWidth) {
                this.scrollBarWidth = self.getScrollBarWidth();
                winwidth = window.innerWidth;
            }
        });
        this.sidebar();
        this.dropdown();
        this.popup();
        this.builDynamicTabs();
        this.effectLabel();
        this.tooltips();
        this.customCheckbox();
        $('body').on('contentUpdated', function() {
            self.tooltips();
        });
    };
    if (document.readyState == 'complete') {
        cdzUtilities.init();
    } else {
        $(document).ready(function() {
            cdzUtilities.init();
        });
    }
});