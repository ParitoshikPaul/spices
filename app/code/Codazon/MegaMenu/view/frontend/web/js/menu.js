/**
 * Copyright © 2018 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery', 'jquery-ui-modules/widget'], function($, domReady) {
    $.widget('codazon.megamenu', {
        options: {
            type: 'horizontal',
            fixedLeftParent: '.cdz-fix-left',
            dropdownEffect: 'normal',
            rtlClass: 'rtl-layout',
            verClass: 'cdz-vertical-menu',
            toggleMenuClass: 'cdz-toggle-menu',
            horClass: 'cdz-horizontal-menu',
            parent: '.parent',
            subMenu: '.groupmenu-drop, .cat-tree .groupmenu-nondrop',
            triggerClass: 'dropdown-toggle',
            stopClickOnPC: true,
            delay: 100,
            contPadding: false,
            jslLinks: ['#', 'javascript:void(0)', 'javascript:;'],
            tabletLinkText: '<span class="link-prefix">Go to</span> <span class="link-text">%1</span>',
            tabletMinWidth: 768,
            tabletMaxWidth: 1024,
            pagingMenu: true,
            responsive: {
                768: 'mobile',
            }
        },
        _create: function() {
            var self = this,
                $body = $('body'),
                conf = this.options;
            if (conf.extraCss) {
                $.each(conf.extraCss, function(cssId, file) {
                    if ($('#'+cssId).length == 0) $('<link id="'+cssId+'" rel="stylesheet" type="text/css"  media="all" href="'+file+'" />').prependTo('head');
                });
            }
            if (conf.useAjaxMenu) {
                $.ajax({
                    url: conf.ajaxUrl,
                    data: {menu: conf.menu, paging_menu: conf.pagingMenu, menu_type: conf.type},
                    type: 'GET',
                    cache: true,
                    success: function(rs) {
                        rs.success ? self.element.replaceWith(rs.html) : rs ? self.element.replaceWith(rs) : null;
                        $('body').trigger('contentUpdated');
                    }
                });
                return;
            }                
            this.options.trigger = '.' + conf.triggerClass;
            if(self.element.parents(conf.fixedLeftParent).length == 0){
                self.element.parent().addClass(conf.fixedLeftParent.replace('.',''));
                var $fixedLeftParent = self.element.parent();
            }else{
                var $fixedLeftParent = self.element.parents(conf.fixedLeftParent).first();
            }
            if(conf.contPadding === false){
                conf.contPadding = parseInt($fixedLeftParent.css('padding-left'));
            }
            var $menu = self.element;
            if(conf.type == 0){
                self._dropdownWidthStyle();
            }
            if ($menu.hasClass(conf.horClass) || $menu.parents('[data-action="navigation"]').length) {
                self._assignControls()._listen();
            }
            if ($menu.hasClass(conf.horClass)) {
                conf.pagingMenu ? self._hideOverflowItems() : null;
                if ($body.hasClass(conf.rtlClass) || ($menu.parents('.'+conf.rtlClass).length > 0)) {
                    self._alignMenuRight(conf);
                } else {
                    self._alignMenuLeft(conf);
                }
                self._assignControls()._listen();
            } else if ($menu.hasClass(conf.verClass) && (!$menu.hasClass('cdz-scroll-menu'))) {
                self._alignMenuTop();
            }
            self._currentMode = self._getMode();
            self._rebuildHtmlStructure();
            self._setupMenu();
            self._responsive();
            self._lazyImages();
            if (conf.type != 1) {
                self._dropdownEffect();
            }
            self._menuTabs();
            self.element.removeClass('no-loaded');
            if (self.element.hasClass('cdz-scroll-menu')) {
                self.element.addClass('no-loaded');
                require(['Codazon_MegaMenu/js/scroll-menu'], function(scrollMenu) {
                    conf.menuWidget = self;
                    scrollMenu(conf, self.element);
                    self.element.removeClass('no-loaded');
                });
            }
            $('body').trigger('cdzmenu.initialized', [this.element]);
        },
        _lazyImages: function() {
            var self = this, conf = this.options;
            $('.item.level0', this.element).one('mouseover.lazyimages', function() {
                $('[data-menulazy]', $(this)).each(function() {
                    var $img = $(this), src = $img.attr('data-menulazy'); $img.on('load', function() {$img.removeAttr('data-menulazy');}).attr('src', src);
                });
            });
        },
        _isToggleMenu: function() {
            return this.element.hasClass(this.options.toggleMenuClass);
        },
        _hideOverflowItems: function() {
            var self = this;
            var $ul = self.element.children('.groupmenu:first'), $staticWrap = $('<div class="static-wrap" style="position: static;">');
            $originalLis = $ul.children();
            self.overFlowItems = [];
            $staticWrap.insertBefore(self.element);
            self.element.appendTo($staticWrap);
            var $controlWrap = $('<div style="display:none" class="switchpage-control" style="display:none;" />'),
            $control = $('<a class="control" href="#"><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="times"></span></a>'),
            $overFlowUl = $('<ul style="display:none" class="groupmenu overflow-items-container">');
            $controlWrap.appendTo($staticWrap);
            $control.appendTo($controlWrap);
            $overFlowUl.insertAfter($ul);
            function getOverFlowItems() {
                var ulWidth = $ul.innerWidth(), lisWidth = 0, overFlowItems = [], overflow = false;
                $originalLis.each(function(id, el) {
                    var $li = $(this);
                    lisWidth = lisWidth + $li.outerWidth(true);
                    if (overflow) {
                        overFlowItems.push($li.get(0));
                    } else {
						if (lisWidth > ulWidth) {
                            overFlowItems.push($li.get(0));
                            overflow = true;
                        } else if (lisWidth + 60 > ulWidth) { /* 60 is width of controller */
							if (($li.next().length) && ($li.outerWidth(true) > 60)) {
                                overFlowItems.push($li.get(0));
                                overflow = true;
                            }
						}
                    }
                });
                return overFlowItems;
            }
            function restoreOrigin() {
                $control.removeClass('page-two');
                $staticWrap.removeClass('has-items-overflowed').find('.hide-page').removeClass('hide-page');
                $staticWrap.find('.show-page').removeClass('show-page');
                $controlWrap.hide();
                $overFlowUl.hide();
                $originalLis.prependTo($ul);
            }
            function moveOverFlowItems() {
                restoreOrigin();
                self.overFlowItems = getOverFlowItems();
                if (self.overFlowItems.length) {
                    $staticWrap.addClass('has-items-overflowed');
                    $ul.addClass('show-page')
                    $overFlowUl.addClass('hide-page').css('display','');
                    $.each(self.overFlowItems, function(i, el) {
                        var $overFlowItem = $(el);
                        $overFlowItem.appendTo($overFlowUl);
                    });
                    $controlWrap.css({display: ''});
                }
            }
            var currentMode = self._getMode();
            if (currentMode != 'mobile') {
                moveOverFlowItems();
            }
            var winWidth = window.innerWidth;
            var t = false;
            function prepareMenu() {
                if (t) clearTimeout(t);
                if (self._getMode() != 'mobile') {
                    currentMode = self._getMode();
                    moveOverFlowItems();
                } else {
                    t = setTimeout(function() {
                        if ( (currentMode != 'mobile') && self._getMode() == 'mobile') {
                            restoreOrigin();
                            currentMode = 'mobile';
                        }
                    }, 100);
                }
                winWidth = window.innerWidth;
            }
            $(window).on('resize', function() {
                if (winWidth != window.innerWidth) {
                    prepareMenu();
                }
            }).on('changeHeaderState', function() {
                if (self._getMode() != 'mobile') {
                    moveOverFlowItems();
                }
            });
            var at = false;
            $control.on('click', function(e) {
                e.preventDefault();
                $overFlowUl.css('display', '');
                var $hidepage = $staticWrap.find('.hide-page'),
                $showpage = $staticWrap.find('.show-page'),
                dott = [];
                $hidepage.removeClass('hide-page').addClass('show-page animated');
                $showpage.removeClass('show-page').addClass('hide-page animated');
                $control.find('.dot').removeClass('wave-line').each(function(i, el) {
                    var $dot = $(this);
                    if (dott[i]) {
                        clearTimeout(dott[i]);
                    }
                    dott[i] = setTimeout(function() {
                        $dot.addClass('wave-line');
                        dott[i] = setTimeout(function() {
                            $dot.removeClass('wave-line');
                        }, 1000);
                    }, i*150);
                });
                if (at) clearTimeout(at);
                at = setTimeout(function() {
                    $staticWrap.find('.animated').removeClass('animated');
                    var ofulWidth = $overFlowUl.outerWidth(),
                    ofulHeight = $overFlowUl.height();
                    ofliWidth = 0;
                    $overFlowUl.children().each(function() {
                        ofliWidth += $(this).outerWidth(true);
                    });
                    if (ofliWidth > ofulWidth) {
                        $overFlowUl.css({
                            overflow: 'hidden',
                            maxHeight: ofulHeight,
                            display: 'block',
                            opacity: 0
                        }).animate({
                            maxHeight: 2.1*ofulHeight,
                            opacity: 1
                        }, 300, 'swing', function() {
                            $overFlowUl.css({overflow: '', maxHeight: '', opacity: ''});
                        });
                    }
                }, 1050);
                $control.toggleClass('page-two');
            });
        },
        _menuTabs: function() {
            var self = this;
            $('.menu-tabs', self.element).each(function(){
                var $tabs = $(this);
                if($tabs.parents('.tab-item').length > 0){
                    var $tabParent = $tabs.parents('.tab-item').first().find('.cdz-tab-pane').find(' > .groupmenu-drop-content').first();
                    if(typeof $tabs.data('attached') === 'undefined'){
                        var $liParent = $tabs.parents('.item').first();
                        $tabs.data('attached',true);
                        $liParent.prevAll().each(function(){
                            var $prev = $(this);
                            $prev.appendTo($tabParent);
                            $prev.children().first().unwrap();
                        });
                        var checkTab = false;
                        $tabs.prevAll().appendTo($tabParent);
                        $tabs.appendTo($tabParent);
                        $liParent.nextAll().each(function(){
                            var $next = $(this);
                            if($next.children('.menu-tabs').length > 0){
                                checkTab = true; return false;
                            }
                        });
                        if(!checkTab){
                            $liParent.nextAll().each(function(){
                                var $next = $(this);
                                $next.appendTo($tabParent);
                                $next.children().first().unwrap();
                            });
                        }
                        $liParent.remove();
                    }
                }
            });
            
            $('.menu-tabs', self.element).each(function(){
                var $tabs = $(this);
                var html = '';
                if($tabs.parents('.menu-tabs').length > 0){
                    var leftClass = 'col-sm-6';
                    var rightClass = 'col-sm-18';
                }else{
                    var leftClass = 'col-sm-5';
                    var rightClass = 'col-sm-19';
                }
                html += '<div class="row cdz-tabs">';
                html +=     '<div class="'+($tabs.hasClass('cdz-vertical-tabs')?leftClass:'')+' cdz-nav-tabs"></div>';
                html +=     '<div class="'+($tabs.hasClass('cdz-vertical-tabs')?rightClass:'')+' cdz-tab-content"></div>';
                html += '</div>';
                var $tabInner = $(html);
                var $accordion = $('> .groupmenu-nondrop',$tabs);
                $tabInner.appendTo($tabs);
                var tabLinks = [];
                var tabPanes = [];
                
                $('> .tab-item',$accordion).each(function(index, element) {
                    var $tabItem = $(this), $tabLink = $('> .cdz-link-wrap > .cdz-tab-link',$tabItem), $tabPane = $('> .cdz-tab-pane',$tabItem);
                    if (!$tabLink.attr('href')) {
                        $tabLink.attr('href', 'javascript:void(0)');
                    }
                    tabLinks.push($tabLink);
                    tabPanes.push($tabPane);
                });
                
                $('> .tab-item',$accordion).each(function(){
                    var $tabItem = $(this), $tabLink = $('> .cdz-link-wrap > .cdz-tab-link',$tabItem), $tabPane = $('> .cdz-tab-pane',$tabItem);
                    $tabLink.on('mouseenter.cdztabs',
                        function(e){
                            $(tabLinks).each(function(){ $(this).removeClass('active') });
                            $(tabPanes).each(function(){ $(this).removeClass('active') });
                            $tabPane.addClass('active');
                            $tabLink.addClass('active');
                        }
                    );
                    $tabLink.appendTo($('> .cdz-nav-tabs',$tabInner));
                    $tabPane.appendTo($('> .cdz-tab-content',$tabInner));
                });
                $('.cdz-tab-pane',$tabInner).first().addClass('active');
                $('.cdz-tab-link',$tabInner).first().addClass('active');
                $accordion.hide();
                function pcTabs(){
                    $accordion.hide();
                    $tabInner.show();
                    $('> .tab-item',$accordion).each(function(id,el){
                        var $tabItem = $(this);
                        var $tabLink = tabLinks[id], $tabPane = tabPanes[id];
                        $('.dropdown-toggle',$tabLink.parent()).remove();
                        $tabLink.appendTo($('> .cdz-nav-tabs',$tabInner));
                        $tabPane.appendTo($('> .cdz-tab-content',$tabInner));
                        $tabPane.css('display','');
                        $tabPane.removeClass('active');
                        $tabLink.removeClass('active');
                        $tabLink.on('mouseenter.cdztabs',
                            function(e){
                                $(tabLinks).each(function(){ $(this).removeClass('active') });
                                $(tabPanes).each(function(){ $(this).removeClass('active') });
                                $tabPane.addClass('active');
                                $tabLink.addClass('active');
                            }
                        );
                    });
                    tabLinks[0].addClass('active');
                    tabPanes[0].addClass('active');
                }
                function mbTabs(){
                    $accordion.show();
                    $tabInner.hide();
                    $('> .cdz-nav-tabs > .cdz-tab-link',$tabInner).each(function(id,el){
                        var $tabLink = $(this), $tabPane = tabPanes[id],
                        $tabItem = $('> .tab-item:eq('+id+')',$accordion),
                        $linkWrap = $('> .cdz-link-wrap',$tabItem);
                        var $toggle = $('<span class="dropdown-toggle"></span>');
                        $tabLink.off('mouseenter.cdztabs').appendTo($linkWrap);
                        $tabPane.appendTo($tabItem).hide().removeClass('active');
                        $tabLink.removeClass('active');
                        $toggle.appendTo($linkWrap).on('click',function(){
                            $tabLink.toggleClass('active');
                            $tabPane.slideToggle(200,function(){
                                $tabPane.toggleClass('active').height('');
                            });
                        });
                    });
                }
                var currentMode = self._getMode();
                if (currentMode == 'mobile') {
                    mbTabs();
                }
                function tabMinHeight(tabPanes){
                    var maxHeight = 0;
                    $(tabPanes).each(function(){
                        $(this).css({minHeight:''});
                        $(this).show();
                    });
                    $(tabPanes).each(function(id,el){
                        var $childPane = $(tabPanes[id]).find('.cdz-tab-pane');
                        if($childPane.length){
                            tabMinHeight($childPane);
                        }
                        if($(tabPanes[id]).height() > maxHeight){
                            maxHeight = $(tabPanes[id]).height();
                        }
                    });
                    $(tabPanes).each(function(){
                        $(this).css({minHeight:maxHeight});
                        $(this).css('display','');
                    });
                }
                $tabs.parents('li.level0').first().find('> .groupmenu-drop').on('animated',function(){
                    tabMinHeight(tabPanes);
                });
                $tabs.parents('li.level0').hover(
                    function(){
                        if(self._getMode() != 'mobile'){
                            switch(self.options.dropdownEffect){
                                case 'slide':
                                case 'fade':
                                    if(self.options.type == 1){
                                        setTimeout(function(){
                                            tabMinHeight(tabPanes);
                                        },150);
                                    }
                                    break;
                                case 'normal':
                                    setTimeout(function(){
                                        tabMinHeight(tabPanes);
                                    },150);
                                    break;
                                default:
                                    tabMinHeight(tabPanes);                             
                            }
                        }
                    },
                    function(){
                        $(tabPanes).each(function(){
                            $(this).css({minHeight:''});
                        });
                    }
                );
                    
                $(window).on('resize', function() {
                    var mode = self._getMode();
                    if (currentMode != mode) {
                        currentMode = mode;
                        if (mode == 'mobile') {
                            mbTabs();
                        } else {
                            pcTabs();
                        }
                    }
                });
            });
        },
        _dropdownWidthStyle: function(){
            var self = this;
            var $win = $(window);
            $('body').addClass(fixedLeftClass);
            if(self.element.hasClass('dropdown-fullwidth')){
                self.options.fixedLeftParent = '.cdz-fullwidth-fix-left';
                var fixedLeftClass = self.options.fixedLeftParent.replace('.','');
                if(self.element.parents(self.options.fixedLeftParent).length == 0){
                    $('body').addClass(fixedLeftClass);
                }
                var $container = self.element.parents(self.options.fixedLeftParent).first();
                $('li.item.level0', self.element).each(function(){
                    var $li = $(this);
                    var $drop = $('> .groupmenu-drop', $li);
                    if( !$li.hasClass('no-full')){
                        function setWidth(mode){
                            if (mode == 'desktop') {
                                $drop.width($container.outerWidth(true));
                            } else {
                                $drop.width('');
                            }
                        }
                        var mode = self._getMode();
                        setWidth(mode);
                        $(window).on('resize', function() {
                            mode = self._getMode();
                            setWidth(mode);
                        });
                    }
                });
            }
        },
        _assignControls: function () {
            this.controls = {
                toggleBtn: $('[data-action="toggle-nav"]'),
                swipeArea: $('.nav-sections')
            };
            return this;
        },
        _listen: function () {
            var controls = this.controls;
            var toggle = this.toggle;
            controls.toggleBtn.unbind();
            controls.swipeArea.unbind();
            this._on(controls.toggleBtn, {'click': toggle});
            this._on(controls.swipeArea, {'swipeleft': toggle});
        },
        toggle: function () {
            if (this.mbTo) clearTimeout(this.mbTo);
            if ($('html').hasClass('nav-open')) {           
                this.mbTo = setTimeout(function () {
                    $('html').removeClass('nav-open');
                    setTimeout(function() {
                        $('html').removeClass('nav-before-open');
                    }, 350);
                }, 42);
            } else {
                $('html').addClass('nav-before-open');
                this.mbTo = setTimeout(function () {
                    $('html').addClass('nav-open');
                }, 42);
            }
        },
        _currentMode: false,
        _dropdownEffect: function() {
            var self = this;
            var conf = this.options,
                effect = conf.dropdownEffect;
            switch (effect) {
                case 'translate':
                case 'slide':
                case 'fade':
                default:
                    self._attachEffect(effect);
                    break;
            }
        },
        _attachEffect: function(type) {
                var self = this,
                    conf = this.options;
                var timeout = false;
                $('.level-top', self.element).each(function() {
                    var $leveltop = $(this);
                    var $drop = $leveltop.children('.groupmenu-drop');
                    if (type != 'translate') {
                        $drop.hide();
                    }
                    $drop.addClass('slidedown');
                    $leveltop.hover(function() {
                        $('item.level0',self.element).find(' > .groupmenu-drop').height('');
                        if (timeout) clearTimeout(timeout);
                        timeout = setTimeout(function() {
                            if (self._currentMode == 'desktop') {
                                if (type == 'slide') {
                                    $drop.css({'box-shadow':'none','border-bottom':'none'});
                                    $drop.stop().slideDown(400,'swing', function() {
                                        $leveltop.addClass('open');
                                        $drop.height('');
                                        $drop.trigger('animated');
                                        $drop.css({'box-shadow':'','border-bottom':''});
                                    });
                                } else if (type == 'fade') {
                                    $drop.stop().fadeIn(400,'swing',function() {
                                        $leveltop.addClass('open');
                                        $drop.trigger('animated');
                                    });
                                } else if (type == 'normal') {
                                    $drop.show().trigger('animated');
                                    $leveltop.addClass('open');
                                }
                                $leveltop.trigger('animated_in');
                            }
                        }, conf.delay);
                    }, function() {
                        if (timeout) clearTimeout(timeout);
                        if (self._currentMode == 'desktop') {
                            if (type == 'slide') {
                                $drop.stop().slideUp(200, function() {
                                    $leveltop.removeClass('open').trigger('animated_out');
                                });
                            } else if (type == 'fade') {
                                $drop.stop().fadeOut(200, function() {
                                    $leveltop.removeClass('open').trigger('animated_out');
                                });
                            } else if (type == 'normal') {
                                $drop.hide();
                                $leveltop.removeClass('open').trigger('animated_out');
                            }
                        }
                    });
                });
            },
        _desktopMenu: function(conf) {
            var $menu = this.element, $subMenu = $(conf.subMenu, $menu);
            $subMenu.css('display', '').removeClass('open');
            $(conf.parent, $menu).removeClass('open');
            $(conf.trigger, $menu).remove();
        },
        _mobileMenu: function(conf) {
            var $menu = this.element;
            $(conf.subMenu, $menu).hide();
            $(conf.parent, $menu).each(function() {
                var $li = $(this);
                $li.children(conf.subMenu).each(function() {
                    var $subMenu = $(this);
                    if ($li.children(conf.trigger).first().length == 0) {
                        $('<span class="' + conf.triggerClass + '">').insertBefore($subMenu).on('click.showsubmenu', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                            $subMenu.toggleClass('open').hasClass('open') ? ($li.addClass('open'), $subMenu.slideDown(300)) : ($li.removeClass('open'), $subMenu.slideUp(300));
                        });
                    }
                });
            });
        },
        _getAdapt: function() {
            var responsive = this.options.responsive,
                $win = $(window),
                winWidth = $win.prop('innerWidth'),
                minWidth = 0;
            for (adapt in responsive) {
                if ((minWidth <= winWidth) && (winWidth < adapt)) {
                    return adapt;
                }
                minWidth = adapt;
            }
            return false;
        },
        _getMode: function() {
            responsive = this.options.responsive;
            $win = $(window);
            var winWidth = $win.prop('innerWidth');
            var minWidth = 0;
            var adapt = this._getAdapt();
            if (this._isToggleMenu()) {
                return 'mobile';
            }
            if (adapt !== false) {
                return responsive[adapt];
            } else {
                return 'desktop';
            }
        },
        _setupMenu: function() {
            var mode = this._getMode();
            if (mode == 'mobile') {
                this._mobileMenu(this.options);
            } else {
                this._desktopMenu(this.options);
            }
        },
        _responsive: function() {
            var self = this;
            var windwidth = window.innerWidth;
            this._toggleTabletData();
            $(window).on('resize', function() {
                var mode = self._getMode();
                if (self._currentMode != mode) {
                    self._currentMode = mode;
                    self._setupMenu();
                }
                if (windwidth != window.innerWidth) {
                    windwidth = window.innerWidth;
                    self._toggleTabletData();
                }
            });
        },
        _toggleTabletData: function() {
            if (this._isTabletDevice()) {
                this.element.addClass('is-tablet');
            } else {
                this.element.removeClass('is-tablet');
            }
        },
        _isNotJsLink: function(link) {
            return (this.options.jslLinks.indexOf(link) == -1);
        },
        _isTabletDevice: function() {
            return /(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/.test(navigator.userAgent.toLowerCase());
        },
        _isGeneralTablet: function() {
            return (this.options.tabletMinWidth <= window.innerWidth && window.innerWidth <= this.options.tabletMaxWidth) || this._isTabletDevice();
        },
        _rebuildHtmlStructure: function() {
            var self = this, conf = this.options;
            $('.need-unwrap', self.element).each(function() {
                var $this = $(this);
                $this.children('.groupmenu-drop').removeClass('groupmenu-drop').addClass('groupmenu-nondrop');
                var $parent = $(this).parent();
                var $newDiv = $('<div />').appendTo($parent).attr('class', $this.attr('class')).attr('style', $this.attr('style'));
                $this.children().appendTo($newDiv);
                $this.remove();
            });
            $('.no-dropdown', self.element).each(function() {
                var $noDropdown = $(this);
                $('.need-unwrap', $noDropdown).first().unwrap();
                $('.need-unwrap', $noDropdown).removeClass('need-unwrap');
                $noDropdown.children('.groupmenu-drop').removeClass('groupmenu-drop').addClass('groupmenu-nondrop');
            });
            $('li.item.parent', self.element).each(function() {
                var $li = $(this), $link = $li.children('a'), link = $link.attr('href');
                if (self._isNotJsLink(link)) {
                    var $drop = $li.children('ul.groupmenu-drop');
                    if ($drop.length) {
                        var linkText = $link.text();
                        var $goLink = $link.clone().removeAttr('class').addClass('menu-go-link').wrap('<li class="item tablet-item visible-tablet"></li>');
                        $goLink.html(conf.tabletLinkText.replace('%1', linkText));
                        $goLink.parent().prependTo($drop);
                        $link.on('click', function(e) {
                            if (self._isGeneralTablet() && (!self._isToggleMenu())) {
                                e.stopPropagation();
                                e.preventDefault();
                            }
                        });
                    }
                }
            });
            
            $('.menu-tabs', self.element).each(function() {
                var $tabs = $(this);
                $('.cdz-tab-link', $tabs).each(function(i, el) {
                    var $link = $(this), link = $link.attr('href');
                    if (self._isNotJsLink(link) && link) {
                        $drop = $('.cdz-tab-pane:eq('+ i +')', $tabs);
                        if ($drop.length) {
                            var linkText = $link.text();
                            var $goLink = $link.clone().removeAttr('class').addClass('menu-go-link').wrap('<div class="tablet-tab-link visible-tablet"></div>');
                            $goLink.html(conf.tabletLinkText.replace('%1', linkText));
                            $goLink.parent().prependTo($drop);
                            $link.on('click', function(e) {
                                if (self._isGeneralTablet() && (!self._isToggleMenu())) {
                                    e.stopPropagation();
                                    e.preventDefault();
                                }
                            });
                        }
                    }
                });
            });
        },
        _getEventIn: function(conf) {
            if (this.options.type == 'translate') {
                return 'mouseover mouseenter';
            } else {
                return 'animated_in';
            }
        },
        _getEventOut: function(conf) {
            if (this.options.type == 'translate') {
                return 'mouseleave';
            } else {
                return 'animated_out';
            }
        },
        _alignMenuLeft: function(conf) {
            var self = this;
            var $menuCont = self.element.parents(conf.fixedLeftParent).first();
            function handlerIn($li){
                var $drop = $li.children('.groupmenu-drop').first(), dpl = $drop.get(0).style.display;
                $drop.css({'display':'block', 'opacity':0, 'visibility':'hidden'});
                var dWidth = $drop.outerWidth(), dOffset = $li.offset().left,
                cWidth = $menuCont.width(), cOffset = $menuCont.offset().left,
                dRightBound = dOffset + dWidth, cRightBound = cOffset + cWidth,
                overFlow = dRightBound - cRightBound;
                if(overFlow > 0){
                    var relativeLeft = dOffset - cOffset;
                    var adjustment = self.element.offset().left - cOffset;
                    if (adjustment > 10) adjustment = 0;
                    if( (cWidth - dWidth) <= 20 && ((cWidth - dWidth) > 0) ){
                        adjustment = (cWidth - dWidth)/2;
                    }                    
                    var left = -Math.min(relativeLeft,overFlow) - Math.max(0, adjustment);
                    $drop.css({left:left});
                }
                $drop.css({'display': dpl, 'opacity':'', 'visibility':''});                
            }
            var $li = $(' > .groupmenu > .level-top.parent > .groupmenu-drop', self.element).parent();
            $li.each(function(){
                var $drop = $li.children('.groupmenu-drop'), $curLi = $(this), timeoute = false;
                handlerIn($curLi);
                $(window).on('load', function() {
                    handlerIn($curLi);
                }).on('cdz_window_width_changed',function(){
                    $drop.css('left','');
                    if(timeoute) clearTimeout(timeoute);
                    timeoute = setTimeout(function(){
                        handlerIn($curLi);
                    },300);
                });
                
            });
            if (this.options.type == 'translate') {             
                $li.hover(function() {
                    handlerIn($(this));
                }, function() {
                    handlerIn($(this));
                });
            } else {
                var eventIn = this._getEventIn(),
                eventOut = this._getEventOut();
                $li.on(eventIn,function(){
                    handlerIn($(this));
                }).on(eventOut, function() {
                    handlerIn($(this));
                });
            }
        },
        _alignMenuRight: function(conf) {
            var self = this;
            var eventIn = this._getEventIn(),
                eventOut = this._getEventOut();
            var $menuCont = self.element.parents(conf.fixedLeftParent).first();
            function handlerIn($li){
                var $drop = $li.children('.groupmenu-drop').first();
                $drop.css({'display':'block', 'opacity':0, 'visibility':'hidden'});
                var dWidth = $drop.outerWidth(), lOffset = $li.offset().left, lWidth = $li.outerWidth(true),
                cWidth = $menuCont.width(), cOffset = $menuCont.offset().left,
                dLeftBound = lOffset + lWidth - dWidth,
                cLeftBound = cOffset,
                overFlow = cLeftBound - dLeftBound;
                var dLeft = -(dWidth - lWidth);
                if(overFlow > 0){
                    var relativeLeft = lOffset - cOffset;
                    var adjustment = self.element.offset().left - cOffset;
                    if(adjustment > 10) {
                        adjustment = 0;
                    }
                    if( (cWidth - dWidth) <= 20 && ((cWidth - dWidth) > 0) ){
                        adjustment = (cWidth - dWidth)/2;
                    }
                    var left = -Math.min(relativeLeft,-dLeft-overFlow) + Math.max(0,adjustment);
                    $drop.css({left:left,right:'auto'});
                }else{
                    $drop.css({left:dLeft,right:'auto'});
                }
                $drop.css({'display':'', 'opacity':'', 'visibility':''});
            }
            var $li = $(' > .groupmenu > .level-top > .groupmenu-drop', self.element).parent();
            $li.each(function(){
                var $drop = $li.children('.groupmenu-drop'), $curLi = $(this), timeoute = false;
                handlerIn($curLi);
                $(window).on('load', function(){
                    handlerIn($curLi);
                }).on('cdz_window_width_changed',function(){
                    $drop.css('left','');
                    if(timeoute) clearTimeout(timeoute);
                    timeoute = setTimeout(function(){
                        handlerIn($curLi);
                    },300);
                });
            });
        },
        _alignMenuTop: function(conf) {
            var self = this;
            var $win = $(window);
            $(' > .groupmenu > .level-top > .groupmenu-drop', self.element).parent().hover(
            function(){
                var $li = $(this);
                var $ddMenu = $(this).children('.groupmenu-drop');
                $ddMenu.css({top:''});
                setTimeout(function() {
                    var winHeight = $win.height(),
                    winTop = $(window).scrollTop();
                    ddTop = $ddMenu.offset().top, ddHeight = $ddMenu.outerHeight(),
                    overflow = (ddTop - winTop + ddHeight) > winHeight;
                    if($li.hasClass('fixtop')){
                        var newTop = parseInt($ddMenu.css('top')) - (ddTop - self.element.children('.groupmenu').offset().top);
                        $ddMenu.css({top: newTop});
                    }else if(overflow){
                        var newTop1 = parseInt($ddMenu.css('top')) - (ddTop - winTop + ddHeight - winHeight);
                        var newTop2 = parseInt($ddMenu.css('top')) - (ddTop - self.element.children('.groupmenu').offset().top);
                        var newTop = Math.max(newTop1,newTop2); 
                        $ddMenu.css({top: newTop});
                    }
                }, 50);
            },
            function(){});
        }
    });
    return $.codazon.megamenu;
});