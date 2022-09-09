/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 'owlslider', 'themecore', 'Magento_Ui/js/modal/modal'], function($) {
    var deskPrefix = 'desk_', mobiPrefix = 'mobi_';
    var deskEvent = 'cdz_desktop', mobiEvent = 'cdz_mobile';
    var $window = $(window);
    var rtl = $('body').hasClass('rtl-layout');
    var mBreakpoint = 768;
    var winWidthChangedEvent = 'cdz_window_width_changed';
    
    $('body').on('click', '.action-close', function() {
        $('body').css({paddingRight: '', transition: ''}).removeClass('_has-modal');
    });
    
    $.widget('codazon.videoframe', {
        options: {
            url: false,
            dimensionRatio: 0.562,
            playBtn: '[data-role=play-video]',
            loader: '[data-role=video-loader]',
            placeholder: '[data-role=video-placeholder]'
        },
        _create: function() {
            this._assignVariable();
            this._initHTML();
            this.element.find(this.options.loader).remove();
            this.element.removeClass('video-no-loaded');
            this._events();
        },
        _assignVariable: function() {
            var self = this, conf = this.options;
            this.$playBtn = $(conf.playBtn, this.element);
            this.$placeholder = $(conf.placeholder, this.element);
            this.$frameVideo = $('#cdz-video-frame');
            this.paddingBottom =  (conf.dimensionRatio*100) + '%';
            this.video = this._getVideo(conf.url);
            if (this.video.type == 'youtube') {
                this.video.url = '//www.youtube.com/embed/' + this.video.id + '?autoplay=1';
            } else {
                this.video.url = '//player.vimeo.com/video/' + this.video.id + '?autoplay=1';
            }
            this.scrollBarWidth = this._getScrollBarWidth();
        },
        _getScrollBarWidth: function () {
            var $outer = $('<div>').css({visibility: 'hidden', width: 100, overflow: 'scroll'}).appendTo('body'),
                widthWithScroll = $('<div>').css({width: '100%'}).appendTo($outer).outerWidth();
            $outer.remove();
            return 100 - widthWithScroll;
        },
        _initHTML: function() {
            var self = this, conf = this.options;
            if (!this.$frameVideo.length) {
                this.$frameVideo = $('<div class="cdz-video-frame" id="cdz-video-frame">').hide().appendTo($('body'));                
                this.$frameVideo.modal({
                    autoOpen: false,
                    clickableOverlay: false,
                    innerScroll: true,
                    modalClass: 'cdz-video-frame',
                    buttons: [],
                    closed: function() {
                        self.$frameVideo.html('');
                    }
                });
            }
            var phdSrc = this.$placeholder.data('src');
            if (!phdSrc || conf.use_df_placeholder) {
                if (this.video.type === 'youtube') {
                    phdSrc = "//img.youtube.com/vi/" + this.video.id + "/hqdefault.jpg";
                    this.$placeholder.attr('src', phdSrc);
                } else if (this.video.type === 'vimeo') {
                    $.ajax({
                        type: 'GET',
                        url: '//vimeo.com/api/v2/video/' + self.video.id + '.json',
                        jsonp: 'callback',
                        dataType: 'jsonp',
                        success: function (data) {
                            phdSrc = data[0].thumbnail_large;
                            self.$placeholder.attr('src', phdSrc);
                        }
                    });
                }
            } else {
                this.$placeholder.attr('src', phdSrc);
            }
        },
        _events: function() {
            var self = this, conf = this.options;
            this.$playBtn.on('click', function() {
                if (!$('body').hasClass('_has-modal')) {
                    $('body').addClass('_has-modal').css({paddingRight: self.scrollBarWidth});
                }
                var frame = '<iframe frameborder="0" allowfullscreen="1" class="abs-frame-inner" src="' + self.video.url + '"></iframe>';
                var $frameInner = $('<div class="frame-inner abs-frame">');
                $frameInner.html(frame);
                $frameInner.css({paddingBottom: self.paddingBottom});
                self.$frameVideo.html($frameInner);
                self.$frameVideo.modal('openModal');
            });
        },
        _getVideo: function (url) {
            var type, id;
            if (url) {
                id = url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);
                if (id[3].indexOf('youtu') > -1) {
                    type = 'youtube';
                } else if (id[3].indexOf('vimeo') > -1) {
                    type = 'vimeo';
                } else {
                    throw new Error('Video URL not supported.');
                }
                id = id[6];
            } else {
                throw new Error('Missing video URL.');
            }
            return {
                type: type,
                id: id
            };
        }
    });
    
    $.widget('codazon.plugin', {
        _create: function(){
            var self = this;
            $.each(this.options, function(fn, options){
                var namespace = fn.split(".")[0];
                var name = fn.split(".")[1];
                if (typeof $[namespace] !== 'undefined') {
                    if(typeof $[namespace][name] !== 'undefined') {
                        $[namespace][name](options, self.element);
                    }
                }
            });
        }
    });
    return $.codazon.plugin;
});