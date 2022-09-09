define([
    "jquery",
    "tinymce",
    "Magento_Ui/js/modal/modal",
    "prototype",
    "mage/adminhtml/events",
    "mage/adminhtml/wysiwyg/widget"
], function(jQuery, tinyMCE, modal) {
    if (typeof window.Codazon === 'undefined') {
        window.Codazon = {};
    }
    window.CdzEditor = {};
    
    CdzEditor.wysiwyg = {
        options: {
            parent: '[data-role=editor-container]',
            editorQuery: '[data-type=editor]',
            element_id: 'codazon_tmp_editor',
            editorUrl: false,
            overlayShowEffectOptions : null,
            overlayHideEffectOptions : null,
        },
        init: function() {
            var self = this, conf = this.options;
            if (typeof Codazon.editorUrl != 'undefined') {
                this.options.editorUrl = Codazon.editorUrl;
            }
            self.editorLoaded = false;
        },
        open: function(btn) {
            var self = this, conf = this.options;
            self.$curEditor = jQuery(btn).parents(conf.parent).find(conf.editorQuery);
            
            if (conf.editorUrl && self.editorLoaded == false) {
                jQuery.ajax({
                    url: conf.editorUrl,
                    cache: false,
                    data: {
                        store_id: '',
                        element_id: conf.element_id + '_editor',
                    },
                    showLoader: true,
                    dataType: 'html',
                    success: function(data, textStatus, transport) {
                        this.openDialogWindow(data);
                    }.bind(this)
                });
                self.editorLoaded = true;
            } else {
                self.openDialogWindow();
            }
        },
        openDialogWindow: function(data) {
            var self = this, conf = this.options;
            if (typeof data != 'undefined') {
                if (this.modal) {
                    this.modal.html(this.$wysiwygEditor);
                } else {
                    this.modal = jQuery(data).modal({
                        title: 'WYSIWYG Editor',
                        modalClass: 'magento',
                        type: 'slide',
                        buttons: [{
                            text: jQuery.mage.__('Cancel'),
                            click: function () {
                                self.closeDialogWindow(this);
                            }
                        },{
                            text: jQuery.mage.__('Submit'),
                            click: function () {
                                self.okDialogWindow();
                            }
                        }],
                        close: function () {
                            self.closeDialogWindow();
                        }
                    });
                }
            } else {
                var wysiwygObj = eval('wysiwyg' + conf.element_id + '_editor');
                if (tinyMCE.get(wysiwygObj.id)) {
                    tinyMCE.get(wysiwygObj.id).setContent(self.$curEditor.val());
                } else if (tinyMCE.get(wysiwygObj.wysiwygInstance.id)) {
                    tinyMCE.get(wysiwygObj.wysiwygInstance.id).setContent(self.$curEditor.val());
                }
            }

            this.modal.modal('openModal');
            jQuery('#' + conf.element_id + '_editor').val(self.$curEditor.val());
        },
        okDialogWindow: function() {
            var self = this, conf = this.options;
            var wysiwygObj = eval('wysiwyg' + conf.element_id + '_editor');
            varienGlobalEvents.fireEvent('tinymceSubmit', wysiwygObj);
            self.$curEditor.val(jQuery('#' + conf.element_id + '_editor').val());
            this.closeDialogWindow();
        },
        closeDialogWindow: function() {
            var self = this, conf = this.options;
            
            if (typeof varienGlobalEvents != undefined && editorFormValidationHandler) {
                varienGlobalEvents.removeEventHandler('formSubmit', editorFormValidationHandler);
            }
            
            try {
                self.$curEditor.focus();
            } catch (e) {
                
            }
            
            this.modal.modal('closeModal');
            
            Windows.overlayShowEffectOptions = conf.overlayShowEffectOptions;
            Windows.overlayHideEffectOptions = conf.overlayHideEffectOptions;
            
        }
    };
    CdzEditor.wysiwyg.init();
    
    
    
    CdzEditor.template = {
        options: {
            parent: '[data-role=editor-container]',
            editorQuery: '[data-type=editor]',
            element_id: 'codazon_tmp_editor',
            templateUrl: false,
            overlayShowEffectOptions : null,
            overlayHideEffectOptions : null,
        },
        init: function() {
            var self = this, conf = this.options;
            if (typeof Codazon.templateUrl != 'undefined') {
                this.options.templateUrl = Codazon.templateUrl;
            }
            self.templateLoaded = false;
        },
        open: function(btn) {
            var self = this, conf = this.options;
            self.$curEditor = jQuery(btn).parents(conf.parent).find(conf.editorQuery);
            if (conf.templateUrl && self.templateLoaded == false) {
                jQuery.ajax({
                    url: conf.templateUrl,
                    type: 'get',
                    data: {
                        store_id: '',
                        element_id: conf.element_id + '_editor',
                    },
                    showLoader: true,
                    success: function(data, textStatus, transport) {
                        this.openDialogWindow(data);
                        self.templateLoaded = true;
                    }.bind(this)
                })
            } else {
                this.openDialogWindow();
            }
        },
        openDialogWindow: function(data) {
            var self = this, conf = this.options;
            if (typeof data != 'undefined') {
                if (!this.modal) {
                    var $tmpl = jQuery('<div class="tpl-container">');
                    $tmpl.appendTo('body');
                    var $tabs = jQuery('<div class="tpl-tabs">').appendTo($tmpl);
                    $tabs.append(jQuery('#template-list-tmpl').tmpl(data));
                    $ul = jQuery('<ul class="tabs">').prependTo($tmpl);
                    $tabs.find('.tpl-set-item').each(function(id, el) {
                        var $tabItem = jQuery(this);
                        var $tabTitle = jQuery('.set-name', $tabItem);
                        var $li = jQuery('<li class="tab-title">').append($tabTitle);
                        
                        $li.appendTo($ul);
                        $li.on('click', function() {
                            $li.addClass('active').siblings().removeClass('active');
                            $tabItem.fadeIn(300).addClass('active').siblings().hide().removeClass('active');
                        });
                        if (id == 0) {
                            $li.addClass('active');
                            $tabItem.addClass('active');
                        } else {
                            $tabItem.hide();
                        }
                    });
                    
                    this.modal = $tmpl.modal({
                        title: jQuery.mage.__('Template HTML'),
                        modalClass: 'magento',
                        type: 'slide',
                        buttons: [{
                            text: jQuery.mage.__('Cancel'),
                            click: function () {
                                self.closeDialogWindow(this);
                            }
                        }],
                        close: function () {
                            self.closeDialogWindow();
                        }
                    })
                }
            }
            this.modal.modal('openModal');
        },
        closeDialogWindow: function() {
            this.modal.modal('closeModal');
            this.$curEditor = undefined;
        },
        insertTmpl: function(btn) {
            var $btn = jQuery(btn), $tplItem = $btn.parents('[data-role=tpl-item]'), $content = jQuery('[data-role=tpl-content]', $tplItem).first();
            if (this.$curEditor) {
                this.$curEditor.val($content.data('content')).trigger('change');
                this.$curEditor.focus();
            }
            this.closeDialogWindow();
        }
    }
    
    CdzEditor.template.init();
});