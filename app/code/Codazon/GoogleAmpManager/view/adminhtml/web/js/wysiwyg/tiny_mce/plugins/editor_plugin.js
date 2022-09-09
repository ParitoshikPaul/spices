/**
 * Copyright © 2022 Codazon. All rights reserved.
 * See COPYING.txt for license details.
*/
if (window.cdzAmpFunction === undefined) {
    window.cdzAmpFunction = function(tinymce) {
        tinymce.create('tinymce.plugins.cdzampimage', {
            icons: 'variable',
            ampPlaceholder: '\n<div id="amp-content-placeholder" style="display: none;">&nbsp;</div>',
            ampPlaceholder2: '\n<div id="amp-content-placeholder" style="display: none;"></div>',
            ampPlaceholderId: 'amp-content-placeholder',
            bookmark: '<span id="mce_marker" data-mce-type="bookmark">﻿</span>',
            init: function (editor, url) {
                var self = this;
                if (editor.id.includes("_amp_")) {
                    editor.on('BeforeSetContent', function(e) {
                        if (e.target.id == editor.id) {
                            var content = e.content;
                            if (!content.includes(self.ampPlaceholderId)) {
                                if ((content != self.bookmark) && (content != '')) {
                                    content = content.gsub(/<amp-img(.*?)\/amp-img>/i, function (match) {
                                        return match[0].replace('<amp-img', '<img')
                                            .replace('></amp-img>', '>')
                                            .replace(' layout=', ' data-mce-layout=')
                                            .replace(' id=', ' data-mce-id=');
                                    }) + self.ampPlaceholder;
                                    e.content = content;
                                }
                            }
                        }
                    });
                    varienGlobalEvents.attachEventHandler('wysiwygDecodeContent', function (content) {
                        content = self.decodeImages(content);
                        return content;
                    });
                }
            },
            decodeImages: function (content) {
                var self = this;
                if (content.includes(self.ampPlaceholderId)) {
                    return content.gsub(/<img(.*?)>/i, function (match) {
                        if (match[0].search(' id=') === -1) {
                            var attr = (match[0].search('data-mce-layout') == -1) ? ' layout="responsive" ':'';
                            return match[0].replace('<img', '<amp-img')
                                .replace('>', attr + '></amp-img>')
                                .replace(' data-mce-layout=', ' layout=').replace(' data-mce-id=',' id=');
                        } else {
                            return match[0];
                        }
                    }).replaceAll(self.ampPlaceholder, '').replaceAll(self.ampPlaceholder2, '');
                }
                return content;
            }
        });
        tinymce.PluginManager.add('cdzampimage', tinymce.plugins.cdzampimage);
    };
}
if (require.toUrl('tinymce').search('tinymce.min') > -1) {
    require(['tinymce'], function(tinymce) {
        window.cdzAmpFunction(tinymce);
    });
} else {
    require(['tiny_mce_4/tinymce.min'], function(tinymce) {
        window.cdzAmpFunction(tinymce);
    });
}
