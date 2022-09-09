/**
 * Copyright © 2021 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */ 
define(['jquery', 'jquery-ui-modules/widget', 'owlslider', 'themecore'], function($) {
    var deskPrefix = 'desk_', mobiPrefix = 'mobi_', deskEvent = 'cdz_desktop', mobiEvent = 'cdz_mobile', $win = $(window), $body = $('body'),
    rtl = $body.hasClass('rtl-layout'), mBreakpoint = 768, winWidthChangedEvent = 'cdz_window_width_changed', itOneSec = false, nowTimeLoading = false, nowTimeLoaded = false;
    function onNowTimeLoaded(func) {
        if (!nowTimeLoading) {
            nowTimeLoading = true;
            $.ajax({
                url: codazon.dateTimeUrl,
                type: 'get',
                success: function(rs) {
                    if (typeof rs.now != 'undefined') codazon.now = rs.now;
                    codazon.localNow = (new Date()).getTime();
                    nowTimeLoaded = true;
                    updateTimestamp();
                    $win.trigger('nowTimeLoaded');
                }
            });
        }
        if (nowTimeLoaded) {
            updateTimestamp(); func();
        } else {
            $win.on('nowTimeLoaded', func);
        }
    };
    function intervalOneSec(func) {
        if (!itOneSec) itOneSec = setInterval(function() {
            $win.trigger('itOneSec');
        }, 1000);
        $win.on('itOneSec', func);
    };
    function itemEffect($parent, delayUnit) {
        $('.cdz-transparent', $parent).each(function(i, el) {
            var $item = $(el);
            setTimeout(function() {
                $item.removeClass('cdz-transparent').addClass('cdz-translator');
                setTimeout(function() {
                    $item.removeClass('cdz-translator');
                }, 1000);
            }, delayUnit*i);
        });
    };
    function formatDate(str) {
        return str.replaceAll('-', '/');
    };
    function updateTimestamp() {
        codazon.curTimestamp = (new Date(formatDate(codazon.now))).getTime() + ((new Date()).getTime() - codazon.localNow);
    };
    function getCustomStyleElement () {
        var $css = $('#cdz-widget-css-script');
        if (!$css.length) $css = $('<style id="cdz-widget-css-script">').appendTo('body');
        return $css;
    }
    $.widget('codazon.buyNow', {
        _create: function() {
            var self = this;
            var $form = self.element.parents('form').first();
            this.element.on('click', function(e) {
                $form.one('addToCartBegin', function() {
                    $form.attr('buy_now', 1);
                }).one('addToCartCompleted', function() {
                    $form.removeAttr('buy_now');
                    window.location = codazon.checkoutUrl;
                });
            });
        }
    });
    $.widget('codazon.autowidth', {
        options: {
            item: '[data-role=item]',
            itemsPerRow: [],
            margin: 0,
            marginBottom: false,
            sameHeight: [],
        },
        _sameHeight: function() {
            var self = this, conf = this.options, maxHeight = 0;
            self.element.attr('data-sameheight', conf.sameHeight.join(','));
            $.each(conf.sameHeight, function(i, sameHeight) {
                self.element.find(sameHeight).css({minHeight: ''}).each(function() {
                    var $sItem = $(this), height = $sItem.outerHeight();
                    if (height > maxHeight) {
                        maxHeight = height;
                    }
                }).css({minHeight: maxHeight});
            });
        },
        _create: function() {
            var self = this, conf = this.options, i = 0;
            if (!conf.itemsPerRow) return true;
            self.itemsPerRow = [];
            for(var point in conf.itemsPerRow) {
                self.itemsPerRow[i] = {};
                self.itemsPerRow[i]['breakPoint'] = point;
                self.itemsPerRow[i]['items'] = conf.itemsPerRow[point];
                i++;
            };
            this.gridId = Math.random().toString().substr(2, 6);
            this._addGridCSS();
            this._itemEffect();
            $body.on('contentUpdated', function() {
                var itemClass = 'cdz-grid-item-' + self.gridId;
                self.element.find(conf.item).addClass(itemClass)
                self._itemEffect();
            });
            self._sameHeight();
            self.element.parents('.no-loaded').first().removeClass('no-loaded');
        },
        _itemEffect: function() {
            itemEffect(this.element, 200);
        },
        _addGridCSS: function() {
            var self = this, conf = this.options, id = this.gridId, parentClass = 'cdz-grid-' + id, itemClass = 'cdz-grid-item-' + id;
            self.element.find(conf.item).addClass(itemClass).first().parent().addClass(parentClass);
            var css = this._getCSSCode(parentClass, itemClass, self.itemsPerRow);
            css = '<style type="text/css">' + css + '</style>';
            $(css).insertAfter(self.element);
        },
        _getCSSCode: function(parentClass, itemClass, itemsPerRow) {
            var self = this, conf = this.options;
            var css = '', width;
            bpLength = itemsPerRow.length;
            var marginSide = rtl ? 'margin-left' : 'margin-right';
            for(var i = bpLength - 1; i >=0; i--) {
                if (itemsPerRow[i].breakPoint < mBreakpoint) {
                    var margin = 10, subtrahend = 11;
                } else {
                    var margin = conf.margin, subtrahend = conf.margin;
                }
                var marginBottom = conf.marginBottom ? conf.marginBottom : margin;
                width = 100/itemsPerRow[i].items;
                css += '@media (min-width: ' + itemsPerRow[i].breakPoint + 'px)';
                if (typeof itemsPerRow[i + 1] != 'undefined') {
                     css += ' and (max-width: ' + (itemsPerRow[i + 1].breakPoint - 1) + 'px)';
                }
                css += '{';
                css += '.' + parentClass + '{' + marginSide +': -' + margin + 'px}';
                css += '.' + parentClass + ' .' + itemClass + '{width:calc(' + width + '% - ' + subtrahend + 'px);' + marginSide +':' + margin + 'px;margin-bottom:' + marginBottom + 'px}';
                css += '}\n';
            };
            return css;
        }
    });
    $.widget('codazon.socialSharing', {
        _create: function() {
            this._bindEvents();
        },
        _bindEvents: function() {
            var self = this, conf = this.options;
            this.element.on('click', '[data-type]', function(e) {
                e.preventDefault();
                var $button = $(this), type = $button.data('type');
                self._openPopup(type);
            });
        },
        _openPopup: function(type) {
            var self = this, conf = this.options;
            var windowStyle = 'menubar=1,resizable=1,width=700,height=600';
            if (type == 'facebook') {
                window.open('https://www.facebook.com/sharer/sharer.php?u=' + conf.url, '', windowStyle);
            } else if (type == 'twitter') {
                window.open('https://twitter.com/intent/tweet?url=' + conf.url + '&text=' + conf.description, '', windowStyle);
            } else if (type == 'pinterest') {
                window.open('https://www.pinterest.com/pin/create/a/?url=' + conf.url + '&media=' + conf.media + '&description=' + conf.description, '', windowStyle);
            } else if (type = 'linkedin') {
                window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + conf.url + '&title=' + conf.title + '&summary=' + conf.description, '', windowStyle);
            }
        }
    });
    $.widget('codazon.flexibleSlider', {
        options: {
            mbMargin: 10,
            sameHeight: ['.product-details', '.product-item-details'],
            pageNumber: false,
            divider: '/',
            pullDrag: true,
            noLoadedClass: false
        },
        _create: function() {
            var self = this, conf = this.options, slideConf = conf.sliderConfig, $el = self.element;
            this.$css = getCustomStyleElement(); this.id = 'cdz-slider-' + themecore.uniqid(); $el.addClass(this.id);
            conf.noLoop = conf.forceNoLoop ? conf.forceNoLoop : $el.hasClass('product-items') || $el.parents('.product-items').length;
            self.totalItem = $el.children().length;
            if (conf.noLoadedClass) {
                $el.parents('.' + conf.noLoadedClass).removeClass(conf.noLoadedClass);
            }
            slideConf.rtl = rtl;
            slideConf.lazyLoad = true;
            slideConf.pullDrag = conf.pullDrag;
            slideConf.navElement = 'div';
            slideConf.autoplayHoverPause = true;
            if (slideConf.responsive) {
                var forceNext = false, side = rtl ? 'left' : 'right', overflow = 'visible';
                $.each(slideConf.responsive, function(i, rsp) {
                    if (slideConf.margin > conf.mbMargin) {
                        if (i < mBreakpoint) {
                            slideConf.responsive[i] = $.extend({}, {margin: conf.mbMargin}, slideConf.responsive[i]);
                        }
                    }
                    if (conf.noLoop) {
                        var items = parseFloat(rsp.items), intItems = parseInt(items), pdr = 0;
                        if (intItems != items) {
                            slideConf.responsive[i].nav = false;
                            if (conf.noLoop) {
                                slideConf.responsive[i].items = intItems;
                                slideConf.responsive[i].loop = false;
                                pdr = ((items - intItems)*100/items) + '%';
                                forceNext = true;
                                self.$css.append('@media (min-width: '+i+'px) {.'+self.id+'{padding-'+side+': '+pdr+';overflow:hidden}.'+self.id+'>.owl-stage-outer{overflow:visible}}');
                            } else {
                                slideConf.responsive[i].loop = true;
                            }
                        } else if (forceNext && conf.noLoop) {
                            forceNext = false;
                            self.$css.append('@media (min-width: '+i+'px) {.'+self.id+'{padding-'+side+': '+pdr+';overflow:'+overflow+'}.'+self.id+'>.owl-stage-outer{overflow:hidden}}');
                        } else if (conf.noLoop) {
                            forceNext = false;
                        }
                    } else {
                        if ((slideConf.responsive[i].items%1) > 0) {
                            slideConf.responsive[i].loop = true;
                        } else {
                            slideConf.responsive[i].loop = slideConf.loop || false;
                        }
                    }
                });
            }
            slideConf.onLoadedLazy = function(e) {$(e.element).css('opacity','').removeClass('owl-lazy cdz-lazy');};
            $el.addClass('owl-carousel').owlCarousel(slideConf);
            self._sameHeight();
            self._itemEffect();
            if (conf.pageNumber) {
                self._addPageNumber();
            }
            if (window.innerWidth <= 1024) {
                setTimeout(function() {
                    $el.trigger('refresh.owl.carousel');
                    self._sameHeight();
                }, 100);
            }
            if (slideConf.autoplay && (!slideConf.loop)) {
                $el.on('translated.owl.carousel', function(e) {
                    var timeout = slideConf.autoplayTimeout ? slideConf.autoplayTimeout : 5000;                    
                    if ($el.find('.owl-item').last().hasClass('active')) {
                        setTimeout(function() {
                            $el.trigger('to.owl.carousel', [0, 0]);
                        }, timeout);
                    }
                });
            }
            if (!slideConf.autoplay) {
                $el.on('changed.owl.carousel', function(e) {
                    $el.trigger('stop.owl.autoplay');
                });
            }
        },
        _addPageNumber: function() {
            var self = this, conf = this.options, owlData = self.element.data('owl.carousel');
            this.$pageNumber = $('<div class="owl-page">').html('<span class="current-page"></span>'+conf.divider+'<span class="total-page"></span>').insertBefore(self.element.find('.owl-nav').first());
            var $current = self.$pageNumber.find('.current-page').text(owlData._current + 1);
            self.$pageNumber.find('.total-page').text(self.totalItem);
            self.element.on('changed.owl.carousel', function(event) {
                $current.text(owlData._current + 1);
            });
        },
        _sameHeight: function() {
            var self = this, conf = this.options;
            self.element.attr('data-sameheight', conf.sameHeight.join(','));
            $.each(conf.sameHeight, function(i, sameHeight) {
                var maxHeight = 0;
                self.element.find(sameHeight).css({minHeight: ''}).each(function() {
                    var $sItem = $(this), height = $sItem.outerHeight();
                    if (height > maxHeight) {
                        maxHeight = height;
                    }
                }).css({minHeight: maxHeight});
            });
        },
        _itemEffect: function() {
            itemEffect(this.element, 200);
        }
    });
    $.widget('codazon.slideshow', {
        _create: function() {
            var self = this, conf = this.options;
            this.$items = this.element.find('[role="items"]');
            this._buildHtml();
            this.$items.addClass('owl-carousel');
            conf.sliderConfig.rtl = rtl;
            conf.sliderConfig.lazyLoad = true;
            conf.sliderConfig.navElement = 'div';
            var vt = false;
            var playVideo = function(e) {
                if (vt) clearTimeout(vt);
                vt = setTimeout(function() {
                    var $active = self.$items.find('.owl-item.active .item');
                    self.$items.find('.video-wrap').empty().off('click.playYT');
                    if ($active.find('.video-wrap:visible').length) {
                        var $video = $active.find('.video-wrap');
                        if ($video.data('video').type === 'youtube') {
                            var frameId = themecore.uniqid('video');
                            $video.html('<div class="abs-frame-inner overlay"></div><div class="abs-frame-inner" noloaded data-videoid="' + $video.data('video').id + '" id="' + frameId + '"></div><div class="abs-frame-inner front-overlay"></div>').addClass('hideall');
                            if (typeof window.onYouTubeIframeAPIReady == 'undefined') {
                                window.onYouTubeIframeAPIReady = function() {
                                    function loadVideo() {
                                        $('.video-wrap [noloaded]').each(function() {
                                            var $frame =  $(this), id = $frame.removeAttr('noloaded').attr('id'), videoId = $frame.data('videoid'), $wrap = $frame.parent();
                                            window[id] = new window.YT.Player(id, {
                                                videoId: videoId,
                                                playerVars: {'autoplay': 1, 'playsinline': 1, 'mute':1, 'loop':1, 'controls': 0, 'playlist': videoId, 'iv_load_policy': 3, 'showinfo' : 0, 'modestbranding' : 1, 'autohide': 1, 'enablejsapi': 1, 'origin': document.URL
                                                },
                                                events: {
                                                    'onReady': function(event) {
                                                        setTimeout(function() {
                                                            $wrap.removeClass('hideall');
                                                        }, 1500)
                                                        window[id].playVideo();
                                                    },
                                                    'onStateChange': function(event) {
                                                        if (window[id].getPlayerState() != YT.PlayerState.PLAYING) {
                                                            window[id].playVideo();
                                                        }
                                                    }
                                                }
                                            });
                                        });
                                    }
                                    loadVideo();
                                    $win.on('cdzLoadYoutubeVideo', loadVideo);
                                }
                            }
                            require(['https://www.youtube.com/iframe_api'], function() { $win.trigger('cdzLoadYoutubeVideo'); });
                        } else {
                            $video.html(self._getFrameHtml($video.data('video').url));
                        }
                    }
                }, 50);
            }
            self.$items.on('initialized.owl.carousel translated.owl.carousel', playVideo).owlCarousel(conf.sliderConfig).parents('.abs-frame').first().css('background', '');
            if (conf.showThumbDots) {
                self.$items.addClass('preview-dots');
                $.each(conf.items, function(i, el) {
                    self.$items.find('.owl-dots .owl-dot:eq(' + i + ')').addClass('thumb-dot').css('background-image', 'url(' + el.smallImg + ')').append($('<div class="dot-img-tt"><div class="abs-img" style="padding-bottom: ' + conf.paddingBottom + '%"><img src="' + el.smallImg + '"></div>'+(el.title?'<div class="tt-title">' + el.title + '</div>':'')+'</div>'));
                });
            }
            if (conf.showThumbNav) {
                self.$items.addClass('preview-nav');
                var $prev = $('<div class="thumb-arrow thumb-prev">').appendTo(self.$items.find('.owl-prev')).append('<div class="thumb-tt"><div class="cdz-banner shine-effect"><img /></div><div class="tt-title"></div></div>');
                var $next = $('<div class="thumb-arrow thumb-next">').appendTo(self.$items.find('.owl-next')).append('<div class="thumb-tt"><div class="cdz-banner shine-effect"><img /></div><div class="tt-title"></div></div>');
                var t = false;
                function attachImg() {
                    var $active = self.$items.find('.owl-item.active .item');
                    $prev.find('img').attr('src', $active.attr('data-thumbprev'));
                    $prev.find('.tt-title').text($active.attr('data-titleprev'));
                    $next.find('img').attr('src', $active.attr('data-thumbnext'));
                    $next.find('.tt-title').text($active.attr('data-titlenext'));
                }
                attachImg();
                self.$items.on('change.owl.carousel', function () {
                    if (t) clearTimeout(t);
                    t = setTimeout(attachImg, 0);
                });
            }
        },
        _buildHtml: function() {
            var self = this, conf = this.options, n = conf.items.length;
            $.each(conf.items, function(i, el) {
                let srcAttr = ((i==0) || (!conf.lazyLoad)) ? 'src="' : 'class="owl-lazy" data-src="', $desc;
                let prev = (conf.items[i-1]) ? conf.items[i-1] : conf.items[n-1], next = (conf.items[i+1]) ? conf.items[i+1] : conf.items[0];
                let video = el.video ? self._getVideo(el.video) : {};
                let $item = $('<div class="item" data-titleprev="'+ prev.title +'" data-titlenext="'+ next.title +'" data-thumbprev="'+prev.smallImg+'" data-thumbnext="'+next.smallImg+'"><a class="item-image abs-img" style="padding-bottom: ' + conf.paddingBottom + '%" href="' + el.link + '"><img alt="'+ el.title +'" ' + srcAttr + el.img + '" /></a> </div>').appendTo(self.$items);
                if ($desc = self.element.find('.item-desc-' + i)) $desc.appendTo($item);
                if (video.type) {
                    let paddingBottom = el.video_ratio ? 100*parseFloat(el.video_ratio) : conf.paddingBottom;
                    $('<div class="video-wrap abs-frame">').css({paddingBottom: paddingBottom + '%'}).attr('data-video', JSON.stringify(video)).appendTo($item);
                }
            });
        },
         _getFrameHtml: function(videoUrl) {
            return '<div class="abs-frame-inner overlay"></div><iframe allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" frameborder="0" allowfullscreen class="abs-frame-inner" src="' + videoUrl + '"></iframe><div class="abs-frame-inner"></div>';
        },
        _getVideo: function (url) {
            var type, id, url;
            if (url) {
                id = url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);
                type = (id[3].indexOf('youtu') > -1) ? 'youtube' : ((id[3].indexOf('vimeo') > -1) ? 'vimeo' : ''); id = id[6];
                url = (type === 'youtube') ?  '//www.youtube.com/embed/'+id+'?autoplay=1&mute=1&enablejsapi=1&controls=0&showinfo=0&modestbranding=1&rel=0&autohide=1&color=white&iv_load_policy=3&loop=1&playlist='+id: '//player.vimeo.com/video/' + id + '?autoplay=1&loop=1&autopause=0&muted=1';
                return {type: type, id: id, url: url};
            }
            return {};
        }
    });
    
    $.widget('codazon.minicountdown', {
        options: {
            nowDate: false,
            startDate: false,
            stopDate: false,
            dayLabel: 'Day(s)',
            hourLabel: 'Hour(s)',
            minLabel: 'Minute(s)',
            secLabel: 'Second(s)',
            hideWhenExpired: true,
            delay: 1000
        },
        _create: function() {
            var self = this; onNowTimeLoaded(function() {self._initHtml();});
        },
        _initHtml: function() {
            var self = this, conf = this.options;
            if (conf.stopDate) {
                conf.stopDate = formatDate(conf.stopDate);                
                var now = codazon.curTimestamp;
                if (conf.startDate) {
                    conf.startDate = formatDate(conf.startDate);
                    self.startDate = new Date(conf.startDate).getTime();
                    if (self.startDate > now) return true;
                }
                self.delta = (new Date()).getTime() - codazon.curTimestamp;
                self.stopDate = (new Date(conf.stopDate)).getTime();
                if (self.stopDate > now) {
                    self.$wrapper = $('<div class="deal-items">').appendTo(self.element.empty()).hide();
                    self.$days = $('<div class="deal-item days"><span class="value" title="'+conf.dayLabel+'"></span> <span class="label">'+conf.dayLabel+'</span></div>').appendTo(self.$wrapper).find('.value');
                    self.$hours = $('<div class="deal-item hours"><span class="value" title="'+conf.hourLabel+'"></span> <span class="label">'+conf.hourLabel+'</span></div>').appendTo(self.$wrapper).find('.value');
                    self.$mins = $('<div class="deal-item mins"><span class="value" title="'+conf.minLabel+'"></span> <span class="label">'+conf.minLabel+'</span></div>').appendTo(self.$wrapper).find('.value');
                    self.$secs = $('<div class="deal-item secs"><span class="value" title="'+conf.secLabel+'"></span> <span class="label">'+conf.secLabel+'</span></div>').appendTo(self.$wrapper).find('.value');
                    self._countDown();
                    intervalOneSec(function() {self._countDown();});
                    self.$wrapper.fadeIn(300, 'linear', function() { self.$wrapper.css({display: ''}); });
                    $body.trigger('cdzResize');
                } else {
                    this._countDownExpired();
                }
            } else {
                this._countDownExpired();
            }
        },
        _countDown: function() {
            var self = this, conf = this.options;
            var now = new Date().getTime() - self.delta, distance = self.stopDate - now;
            if (distance < 0) {
                self._countDownExpired();
            } else {
                var days = Math.floor(distance / (1000 * 60 * 60 * 24)), hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
                mins = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)), secs = Math.floor((distance % (1000 * 60)) / 1000);
                self.$days.text(self._formateNum(days)); self.$hours.text(self._formateNum(hours)); self.$mins.text(self._formateNum(mins)); self.$secs.text(self._formateNum(secs));
            }
        },
        _countDownExpired: function() {
            var self = this, conf = this.options, hwe = conf.hideWhenExpired, $timerWrap = self.element.parents('[role=timer_wrap]');
            $pr = conf.parentSelector ? self.element.parents(conf.parentSelector).first() : $timerWrap;
            if (hwe && self.$wrapper) self.$wrapper.hide();
            $timerWrap.addClass('cd-expired');
            if ($pr.length) {
                if (hwe) $pr.children().hide();
                $expMsg = $pr.find('[role="expired_msg"]');
                if ($expMsg.length) $expMsg.removeClass('hidden').css('display','').appendTo($pr);
            }
            $pr.find('.deal-item .value').html('00');
        },
        _formateNum: function(num) {
            return num.toString().length < 2 ? '0' + num : num;
        }
    });
    
    $.widget('codazon.searchtrigger', {
        options: {
            searchContainer: '#header-search-wrap',
            toggleClass: 'search-opened'
        },
        _create: function() {
            var self = this, conf = this.options;
            var $searchContainer = $(conf.searchContainer);
            var mbSearch = function() {
                $searchContainer.removeClass(conf.toggleClass);
                self.element.removeClass(conf.toggleClass);
            };
            var dtSearch = function() {};
            self.element.on('click.triggersearch', function(e) {
                e.preventDefault();
                $searchContainer.toggleClass(conf.toggleClass);
                if ($searchContainer.hasClass(conf.toggleClass)) {
                    self.element.addClass(conf.toggleClass);
                } else {
                    self.element.removeClass(conf.toggleClass);
                }
            }); 
            $body.on('click', function(e) {
                if ($searchContainer.hasClass(conf.toggleClass)) {
                    var $target = $(e.target);
                    var cond1 = $searchContainer.is($target),
                    cond2 = ($searchContainer.find($target).length > 0),
                    cond3 = self.element.is($target),
                    cond4 = (self.element.find($target).length > 0);
                    if(!(cond1 || cond2 || cond3 || cond4)) {
                        $searchContainer.removeClass(conf.toggleClass);
                        self.element.removeClass(conf.toggleClass);
                    }
                }
            });
            $win.on(deskEvent, dtSearch).on(mobiEvent, mbSearch);
        }
    });
    $.widget('codazon.searchtoggle', {
        options: {
            toggleBtn: '[data-role=search_toggle]',
            searchForm: '[data-role=search_form]',
            toggleClass: 'input-opened',
            mbClass: 'mb-search',
            onlyMobi: true,
            hoverOnDesktop: false
        },
        _create: function () {
            var $element = this.element, conf = this.options,
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
            if (conf.onlyMobi) {
                themecore.isMbScreen() ? mbSearch() : dtSearch();
                $win.on(deskEvent, dtSearch).on(mobiEvent, mbSearch);
            } else {
                mbSearch();
                if (conf.hoverOnDesktop) {
                     $element.hover(
                        function() {
                            if (!themecore.isMbScreen()) $element.addClass(conf.toggleClass);
                        },
                        function() {
                            if (!themecore.isMbScreen()) $element.removeClass(conf.toggleClass);
                        }
                    );
                }
            }
            $searchBtn.on('click', function() {
                if (conf.hoverOnDesktop) {
                    if (themecore.isMbScreen()) $element.toggleClass(conf.toggleClass);
                } else {
                    $element.toggleClass(conf.toggleClass);
                }
            });
        }
    });
    
    $.widget('codazon.isogrid', {
        options: {
            groupStyle: '1,2,2',
            item: '.product-item',
            useDataGrid: true,
            breakPoint: mBreakpoint,
            sameHeight: ['.product-item-details','.product-details'],
            sliderConfig: {},
            colWidth: {1: '40%', 2: '20%', 3: '20%', 4: '20%'}
        },
        _create: function() {
            var self = this, conf = this.options, t = false, ww = window.innerWidth;
            this._assignVariables();
            this._groupItems();
            this._itemEffect();
            if ((conf.groupStyle == '1,3,3,3') || (conf.groupStyle == '3,3,3,1')) {
                $win.on('adaptchange_1200', function() {
                    if (window.innerWidth > conf.breakPoint && ww >= conf.breakPoint) self._groupItems();
                    ww = window.innerWidth;
                });
            }
            $win.on('adaptchange', function() {
                ww = window.innerWidth;
                self._groupItems();
            }).on(winWidthChangedEvent, function() {
                setTimeout(function() { self._sameHeight() }, 300);
            });
        },
        _sumArray: function(array) {
            return array.reduce(function(a, b){return parseFloat(a) + parseFloat(b)});
        },
        _assignVariables: function() {
            var conf = this.options, $el = this.element;
            if(conf.useDataGrid && $el.parents('[data-grid]').length) {
                conf.groupStyle = $el.parents('[data-grid]').data('grid');
            }
            this.subGroup = conf.groupStyle.split(',');
            this.iPG = this._sumArray(this.subGroup);
            this.colPG = this.subGroup.length;
            this.totalItems = $el.children().length;
            this.totalGroup = Math.floor(this.totalItems/this.iPG);
            this.$allItems = $el.find('.product-item');
        },
        _groupItems: function() {
            (window.innerWidth < this.options.breakPoint) ? this._groupItemsOnMb() : this._groupItemsOnPC();
        },
        _itemEffect: function() {
            itemEffect(this.element, 100);
        },
        _groupItemsOnMb: function() {
            var conf = this.options, $inner = $('<div class="mb-group-inner">').appendTo(this.element);
            this.$allItems.each(function(i, el) {
                $(el).appendTo($inner);
            });
            this.element.find('[data-smallimage]').each(function() {
                var $img = $(this);
                $img.attr('src', $img.attr('data-smallimage'));
            });
            this.element.removeClass('hidden').children('.group-inner').trigger('destroy.owl.carousel').remove();
            $.codazon.flexibleSlider({sliderConfig: conf.sliderConfig, sameHeight: []}, $inner);
        },
        _groupItemsOnPC: function() {
            var self = this, conf = this.options, $el = this.element;
            this.Group = [];
            this.$allItems.each(function(i, el) {
                var $item = $(this), groupId = Math.floor(i/self.iPG);
                if (typeof self.Group[groupId] === 'undefined') {
                    self.Group[groupId] = [];
                }
                self.Group[groupId].push($item);
            });
            $el.children('.group-inner').addClass('old').trigger('destroy.owl.carousel');
            var $inner = $('<div class="group-inner">').appendTo($el);
            if ((window.innerWidth < 1200) && ((conf.groupStyle == '1,3,3,3') || (conf.groupStyle == '3,3,3,1'))) {
                var subGroup = [1,2,2,2,2,2];
            } else {
                var subGroup = self.subGroup;
            }
            $el.removeClass('hidden');
            $.each(this.Group, function(i, group) {
                var $group = $('<div class="item-group flex-grid">').appendTo($inner), itemIndex = 0;
                $.each(subGroup, function(ii, iPC) {
                    if (iPC == 1) {
                        var width = (typeof conf.colWidth[iPC] != 'undefined')?(conf.colWidth[iPC]):'50%',
                        colClass = 'large-col';
                    } else {
                        var width = (typeof conf.colWidth[iPC] != 'undefined')?(conf.colWidth[iPC]):'25%',
                        colClass = 'small-col';
                    }
                    var $col = $('<div class="group-col">').appendTo($group).css({width: width}).addClass(colClass);
                    for(var j=0; j < iPC; j++) {
                        if (typeof group[itemIndex] != 'undefined') {
                            group[itemIndex].find('[data-smallimage]').each(function() {
                                var $img = $(this), $gallery = group[itemIndex].find('[data-gallery]');
                                if (iPC == 1) {
                                    if ($gallery.length) {
                                        $gallery.hide();
                                        $.codazon.horizontalThumbsSlider($gallery.data('gallery'), $gallery);
                                        $gallery.removeAttr('data-gallery');
                                    }
                                    $img.attr('src', $img.attr('data-largeimg'));
                                } else {
                                    if ($gallery.length) $gallery.remove();
                                    $img.attr('src', $img.attr('data-smallimage'));
                                }
                            });
                            group[itemIndex].appendTo($col);
                            itemIndex++;
                        }
                    }
                });
                if ((conf.groupStyle == '1,3,3,3') || (conf.groupStyle == '3,3,3,1')) {
                    var groupColWidth = 100 - parseFloat(conf.colWidth[1]),
                    $mergedSubGroup = $('<div class="merged-sub-group">').css('width', groupColWidth + '%');
                    if ((conf.groupStyle == '1,3,3,3')) {
                        $mergedSubGroup.appendTo($group);
                    } else {
                        $mergedSubGroup.prependTo($group);
                    }
                    $group.find('.small-col').css('width', '').appendTo($mergedSubGroup);
                    $.codazon.flexibleSlider({sliderConfig: {
                        margin: 0, dots: false, nav: false,
                        responsive: {
                            768: {items: 2, nav: true}, 1200: {items: 3, pullDrag: false}
                        }},
                        sameHeight: []
                    }, $mergedSubGroup);
                }
            });
            $el.children('.group-inner.old').remove();
            if (self.Group.length > 1) {
                $.codazon.flexibleSlider({sliderConfig: conf.sliderConfig, sameHeight: []}, $inner.addClass('owl-carousel cdz-grid-slider'));
            }
            this._sameHeight();
            $el.find('.img-gallery').css({display: ''});
            $el.find('.mb-group-inner').trigger('destroy.owl.carousel').remove();
        },
        _sameHeight: function() {
            var conf = this.options;
            if (window.innerWidth >= conf.breakPoint) {
                this.element.find('.item-group').each(function() {
                    var $group = $(this);
                    $.each(conf.sameHeight, function(i, sameHeight) {
                        var maxHeight = 0;
                        $group.find('.small-col ' + sameHeight).css({height: '', minHeight: ''}).each(function() {
                            var $sItem = $(this), height = $sItem.outerHeight();
                            if (height > maxHeight) maxHeight = height;
                        }).css({minHeight: maxHeight});
                    });
                });
            } else {
                this.element.find(conf.sameHeight).css({height: ''});
            }
        }
    });
    
    $.widget('codazon.horizontalThumbsSlider', {
        options: {
            parent: '.product-item',
            mainImg: '.product-image-wrapper .product-image-photo:last',
            itemCount: 4,
            activeClass: 'item-active',
            loadingClass: 'swatch-option-loading',
            moreviewSettings: {}
        },
        _create: function(){
            var self = this, conf = this.options;
            if((!conf.images) || (conf.images.length == 0)) return false;
            this.$parent = this.element.parents(conf.parent).first();
            this.$mainImg = $(conf.mainImg, this.$parent);
            this.images = conf.images;
            this.initHtml();
            this.bindHoverEvent();
            this.element.css({minHeight:''});
        },
        initHtml: function(){
            var self = this, conf = this.options;
            this.$slider = $(this.getHtml(this.images));
            this.$slider.appendTo(this.element);
            this.initSlider();
            this.element.css({display: ''});
        },
        initSlider: function() {
            var self = this, conf = this.options;
            var sliderConfig = $.extend({}, {items: 4, nav: true, dots: false, mouseDrag: false, touchDrag: false}, conf.moreviewSettings);
            sliderConfig.responsiveRefreshRate = 200;
            $.codazon.flexibleSlider({sliderConfig: sliderConfig}, this.$slider);
        },
        bindHoverEvent: function(){
            var self = this, conf = this.options;
            $('.gitem', this.$slider).each(function(){
                var $gitem = $(this), $link = $('.img-link', $gitem), $img = $('img', $link), mainSrc = $link.attr('href');
                $link.on('click',function(e){
                    e.preventDefault();
                }).hover(
                    function(){
                        if ($gitem.parents('.owl-carousel.media-slider').length) {
                            $gitem.addClass(conf.activeClass).parent().siblings().children().removeClass(conf.activeClass);
                        } else {
                            $gitem.addClass(conf.activeClass).siblings().removeClass(conf.activeClass);
                        }
                        if(typeof $link.data('loaded') === 'undefined') {
                            var mainImg = new Image();
                            self.$mainImg.addClass(conf.loadingClass);
                            $(mainImg).load(function(){
                                self.$mainImg.removeClass(conf.loadingClass);
                                self.$mainImg.attr('src', mainSrc);
                                $link.data('loaded', true);
                            });
                            mainImg.src = mainSrc;
                        }else{
                            self.$mainImg.attr('src', mainSrc);
                        }
                    }
                );
            });
        },
        getHtml: function(images){
            var self = this, conf = this.options;
            var html =  '<div class="gitems media-slider">';
            $.each(images,function(id,img){
                html += '<div class="gitem"><a class="img-link" href="'+ img.large +'"><img class="img-responsive" src="'+ img.small +'" /></a></div>';
            });
            html += '</div>';
            return html;
        }
    });
    $.widget('codazon.stickyMenu', {
        options: {
            threshold: 300,
            enableSticky: codazon.enableStikyMenu,
            anchor: null,
        },
        _create: function () {
            var self = this, conf = this.options, t = tt = false, w = $win.prop('innerWidth'), $el = this.element;
            if (!conf.enableSticky) return false;
            var $parent = $el.parent(), $anchor = conf.anchor ? $(conf.anchor) : $parent, parentHeight = $anchor.outerHeight(), isHeader = $el.hasClass('js-sticky-menu'),
            headerStateChanged = function() {
                if (isHeader) $win.trigger('changeHeaderState');
                $el.trigger('changeStickyState');
            };
            $parent.css({minHeight: parentHeight});
            var threshold = (window.innerWidth < mBreakpoint) ? $anchor.offset().top + parentHeight : conf.threshold, stop = false, stickyNow = currentState = false;
            this.changeThreshold = function(ths) {threshold = ths;};
            $win.on('resize',function () {
                stop = false;
                if (t) clearTimeout(t);
                t = setTimeout(function () {
                    var newWidth = $win.prop('innerWidth');
                    if (w != newWidth) {
                        $el.removeClass('active'); stop = true;
                        $parent.css({minHeight:''});
                        w = newWidth;
                        headerStateChanged();
                        if (tt) clearTimeout(tt);
                        tt = setTimeout(function() {
                            parentHeight = $anchor.outerHeight();
                            $parent.css({minHeight: parentHeight});
                            threshold = (window.innerWidth < mBreakpoint) ? $anchor.offset().top + parentHeight : conf.threshold;
                            stickyNow = currentState = $win.scrollTop() > threshold;
                            if (currentState) {
                                 $el.addClass('active');
                                 headerStateChanged();
                            }
                        }, 100);
                    }
                }, 50);
            });
            setTimeout(function () {
                $parent.css({minHeight:''});
                $parent.css({minHeight:$parent.height()});
                $win.scroll(function () {
                    currentState = $win.scrollTop() > threshold;
                    if (currentState) {
                        $el.addClass('active');
                    } else {
                        $el.removeClass('active');
                    }
                    if (currentState != stickyNow) {
                        headerStateChanged();
                        stickyNow = currentState;
                    }
                });
            }, 300);
        }
    });
    $.widget('codazon.fullsearchbox', {
        _create:  function() {
            this._attachCategoryBox();
        },
        _attachCategoryBox: function() {
            var self = this, conf = this.options;
            var catHtml = $('#search-by-category-tmpl').html();
            var $catSearch = $(catHtml);
            if ($catSearch.length) {
                this.element.addClass('has-cat-search');
                $catSearch.appendTo(this.element.find('form'));
                $.codazon.categorySearch($catSearch.data('search'), $catSearch);
            } else {
                this.element.addClass('no-cat-search');
            }
        }
    });
    $.widget('codazon.categorySearch', {
        options: {
            trigger: '[data-role="trigger"]',
            dropdown: '[data-role="dropdown"]',
            catList: '[data-role="category-list"]',
            activeClass: 'open',
            currentCat: false,
            allCatText: 'All Categories',
            ajaxUrl: false
        },
        _create: function() {
            this._assignVariables();
            this._assignEvents();
        },
        _assignVariables: function() {
            var self = this, conf = this.options, $el = self.element;
            this.$trigger = $el.find(conf.trigger);
            this.$triggerLabel = this.$trigger.children('span');
            this.$dropdown = $el.find(conf.dropdown);
            this.$catList = $el.find(conf.catList);
            this.$searchForm = $el.parents('form').first().addClass('has-cat');
            this.$catInput = this.$searchForm.find('[name=cat]');
            this.$qInput = this.$searchForm.find('[name=q]');
            if (this.$catInput.length == 0) {
                this.$catInput = $('<input type="hidden" id="search-cat-input" name="cat">').appendTo(this.$searchForm);
            }
            if (conf.currentCat) {
                this.$catInput.val(conf.currentCat);
                var catText = this.$catList.find('[data-id="' + conf.currentCat + '"]').text();
                this.$triggerLabel.text(catText);
            } else {
                this.$catInput.attr('disabled', 'disabled');
            }
            $el.insertBefore(self.$searchForm);
        },
        _assignEvents: function() {
            var self = this, conf = this.options, $el = self.element;
            $body.on('click', '#suggest > li:first > a, .searchsuite-autocomplete .see-all', function(e) {
                e.preventDefault();
                self.$searchForm.submit();
            });
            this.$trigger.on('click', function() {
                $el.toggleClass(conf.activeClass);
            });
            this.$catList.find('a').on('click', function(e) {
                e.preventDefault();
                var $cat = $(this), id = $cat.data('id'), label = $cat.text();
                if (id) {
                    self.$catInput.removeAttr('disabled').val(id).trigger('change');
                    self.$triggerLabel.text(label);
                } else {
                    self.$catInput.attr('disabled', 'disabled').val('').trigger('change');
                    self.$triggerLabel.text(conf.allCatText);
                }
                self.$qInput.trigger('input');
                $el.removeClass(conf.activeClass);
            });
            $body.on('click', function(e) {
                if ($el.has($(e.target)).length == 0) {
                    $el.removeClass(conf.activeClass);
                }
            });
        }
    });
        
    $.widget('codazon.customValidation', {
        _create: function() {
            var self = this;
            require(['validation', 'domReady'], function() {
                self.element.validation();
            });
        }
    });
    
    $.widget('codazon.toggleList', {
        options: {
            item: 'li',
            itemList: 'ul',
            link: 'a'
        },
        _create: function() {
            var self = this, conf = this.options;
            self.element.children(conf.item).addClass('level-top');
            $(conf.item, self.element).each(function() {
                var $item = $(this), $a = $item.children(conf.link);
                if ($item.children(conf.itemList).length) {
                    $item.addClass('parent');
                    var $itemList = $item.children(conf.itemList).hide();
                    $('<span class="menu-toggle">').insertAfter($a).on('click', function() {
                        $itemList.slideToggle(300);
                        $item.toggleClass('active');
                    });
                }
            });
        }
    });
    
    $.widget('codazon.ratingSummary', {
        options: {
            tmpl: '#rating-summary-tmpl'
        },
        _create: function() {
            var self = this, conf = this.options;
            require(['mage/template', 'underscore'], function(mageTemplate) {
                self.tmpl = mageTemplate(conf.tmpl);
                self.$parent = $('.product-info-main .product-reviews-summary').first().find('.rating-summary');
                if (self.$parent.length) {
                    $(self.tmpl({data: conf.data})).appendTo(self.$parent);
                }
            });
        }
    });
    
    $.widget('codazon.innerZoom', {
        options: {
            stage: '.fotorama__stage',
            width: 250,
            height: 250
        },
        _create: function() {
            var self = this, conf = this.options;
            self.element.on('gallery:loaded', function() {
                if (!self.element.data('gallery')) return false;
                self._addMagnifier();
            });
        },
        _addMagnifier: function() {
            var self = this, conf = this.options;
            self.$stage = self.element.find(conf.stage).first();
            self.$magnifier = $('<div class="cdz-magnifier">').css({
                width: conf.width,
                height: conf.height,
                position: 'absolute',
                left: 0,
                top: 0,
            }).appendTo(self.$stage);
            self._manify();
        },
        _manify: function() {
            var self = this, conf = this.options, nativeWidth = 0, nativeHeight = 0, backgroundSize = 0,
            fotorama = self.element.data('gallery').fotorama, t = false;
            self.$stage.on('mousemove.innerZoom', function(e) {
                if (fotorama.activeFrame.type == 'video') {
                    self.$stage.removeClass('cdz-manifier-active');
                    self.$magnifier.hide();
                    return false;
                }
                $mainImg = fotorama.activeFrame.$stageFrame;
                if ($mainImg) {
                    self.$stage.addClass('cdz-manifier-active');
                    if (!nativeWidth && !nativeHeight) {
                        var imgObject = new Image();
                        $(imgObject).on('load', function() {
                            nativeWidth = imgObject.width * conf.zoomRatio;
                            nativeHeight = imgObject.height * conf.zoomRatio;
                            backgroundSize = nativeWidth.toString() + 'px ' + nativeHeight.toString() + 'px';
                        });
                        imgObject.src = fotorama.activeFrame.full;
                    } else {
                        var magnifierOffset = self.$stage.offset(), mx = e.pageX - magnifierOffset.left,
                        my = e.pageY - magnifierOffset.top;
                    }
                    if (mx < self.$stage.width() && my < self.$stage.height() && mx > 0 && my > 0) {
                        self.$magnifier.show();
                        if (t) clearTimeout(t);
                        t = setTimeout(function() {
                            self.$stage.addClass('cdz-manifier-active');
                        }, 100);
                    } else {
                        self.$magnifier.hide();
                        self.$stage.removeClass('cdz-manifier-active');
                    }
                    if (self.$magnifier.is(':visible')) {
                        var dx = $mainImg.offset().left - self.$stage.offset().left, dy = $mainImg.offset().top - self.$stage.offset().top,
                        rx = Math.round(mx / $mainImg.width() * nativeWidth - self.$magnifier.width() / 2) * (-1) + dx,
                        ry = Math.round(my / $mainImg.height() * nativeHeight - self.$magnifier.height() / 2) * (-1) + dy,
                        bgp = rx + "px " + ry + "px", px = mx - self.$magnifier.width() / 2, py = my - self.$magnifier.height() / 2;
                        self.$magnifier.css({
                            left: px,
                            top: py,
                            backgroundImage: 'url("'+fotorama.activeFrame.full+'")',
                            backgroundRepeat: 'no-repeat',
                            backgroundPosition: bgp,
                            backgroundSize: backgroundSize
                        });
                    }
                }
            });
            self.element.on('fotorama:show', function() {
                nativeWidth = 0;
                nativeHeight = 0;
            });
            self.$stage.on('mouseleave.innerZoom', function(e) {
                if (t) clearTimeout(t);
                self.$magnifier.hide();
                t = setTimeout(function() {
                    self.$stage.removeClass('cdz-manifier-active');
                }, 100);
            });
        }
    });
    
    $.widget('codazon.ajaxcmsblock', {
        _create: function() {
            var self = this, conf = this.options;
            if (conf.ajaxUrl && conf.blockIdentifier) {
                $.ajax({
                    url: conf.ajaxUrl,
                    cache: true,
                    data: {block_identifier: conf.blockIdentifier},
                    method: 'get',
                    success: function(rs) {
                        self.element.html(rs);
                        if (typeof conf.afterLoaded == 'function') {
                            conf.afterLoaded();
                        };
                        self.element.trigger('contentLoaded').trigger('contentUpdated');
                    }
                });
            }
        }
    });
    $.widget('codazon.newsletterPopup', {
        _create: function() {
            var self = this, conf = this.options, cookieName = conf.cookieName;
            require(['jquery/jquery.cookie'], function() {
                var checkCookie = $.cookie(cookieName);
                if (!checkCookie) {
                    var date = new Date(), minutes = conf.frequency;
                    date.setTime(date.getTime() + (minutes * 60 * 1000));
                    $.cookie(cookieName, '1', date);
                    setTimeout(function() {
                        var $popup = self.element;
                        $.codazon.ajaxcmsblock({
                            ajaxUrl: conf.ajaxUrl,
                            blockIdentifier: conf.blockIdentifier,
                            afterLoaded: function() {
                               $popup.modal({
                                    autoOpen: true,
                                    buttons: [],
                                    modalClass: 'cdz-newsletter-modal'
                                });
                            }
                        }, $popup);
                    }, conf.delay);
                }
            });
        }
    });
    $.widget('codazon.ajaxcontent', {
        options: {
            cache: true,
            method: 'GET',
            handle: 'replaceWith'
        },
        _create: function(){
            var self = this, conf = this.options;
            $.ajax({
                url: conf.ajaxUrl,
                method: conf.method,
                cache: conf.cache,
                success: function(rs) {
                    var $rs = (self.element[conf.handle])(rs);
                    if (typeof conf.afterLoaded == 'function') {
                        conf.afterLoaded();
                    } else if (typeof conf.afterLoaded == 'string') {
                        eval(conf.afterLoaded)
                    }
                    $body.trigger('contentUpdated');
                    require(['ko'], function(ko) {
                        ko.cleanNode($rs.get(0));
                        if($.fn.applyBindings != undefined) { $rs.applyBindings(); }
                    });
                }
            })
        }
    });
    $.widget('codazon.themewidgets', {
        _create: function(){
            var self = this;
            $.each(this.options, function(fn, options){
                var namespace = fn.split(".")[0];
                var name = fn.split(".")[1];
                if (typeof $[namespace] !== 'undefined') {
                    if ((namespace == 'codazon') && (name == 'slider')) {
                        name = 'flexibleSlider'; /* avoid conflicting with  jquery ui sliders */
                    }
                    if(typeof $[namespace][name] !== 'undefined') {
                        $[namespace][name](options, self.element);
                    }
                }
            });
        }
    });
    return $.codazon.themewidgets;
});