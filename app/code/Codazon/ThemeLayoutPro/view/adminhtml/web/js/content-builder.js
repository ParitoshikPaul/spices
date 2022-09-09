(function($) {
    window.getTmplById = function(id, data, prefix, field_class_prefix) {
        if (typeof prefix != 'undefined') {
            $.each(data, function(i, el) {
                data[i].prefix = prefix;
                data[i].field_class_prefix = field_class_prefix;
            });
        }
        return $('<div></div>').append($('#'+id).tmpl(data));
    }
    $.extend($.tmpl.tag, {
        'var': {
            open: 'var $1;'
        }
    });
    $.widget('codazon.pagebuilder', {
        options: {
            eleArea: '[data-role=ele-area]',
            mainArea: '[data-role=main-area]',
            elTypeTmpl: '#el-type-tmpl',
            elFieldTmpl: '#el-field-tmpl',
            elItemField: '#el-item-field',
            settingBtn: '[data-role=toggle-settings]',
            settingPanel: '[data-role=setting-panel]',
            copyBtn: '[data-role=copy-item]',
            deleteBtn: '[data-role=item-delete]',
            closeBtn: '[data-role=item-close]',
            nextBtn: '[data-role=move-next]',
            prevBtn: '[data-role=move-prev]',
            submitBtn: '[data-role=submit]',
            addToPageBtn: '[data-role=add-to-page]',
            elItem: '[data-role=el-item]',
            elItemChildren: '[data-role=el-children]',
            elItemFields: '[data-role=item-fields]',
            shortDesc: '[data-role=short-desc]',
            sortHandle: ' > .el-item-header > [data-role=sort-handle]',
            inactiveClass: 'el-item-edit-inactive',
            itemTypes: {},
            itemData: {},
            imagePlaceholder: '',
            mediaUrl: '',
        },
        _create: function() {
            this._assignElements();
            this._buildElementTypes();
            this._bindEvents();
            this._buildDropDrag();
            this._loadData();
        },
        _assignElements: function() {
            var self = this, conf = this.options;
            this.$eleArea = $(conf.eleArea, this.element);
            this.$mainArea = $(conf.mainArea, this.element);
            this.elTypeTmpl = conf.elTypeTmpl;
            this.elFieldTmpl = conf.elFieldTmpl;
            this.elItemField = conf.elItemField;
            this.itemTypes = conf.itemTypes;
            this.itemTypesObject = self._convertItemTypes(this.itemTypes);
            this.itemData = conf.itemData;
            this.$currentDrop = false;
            this.elementHTMLtoJson = function($requiredCssInput) {
                return self._elementHTMLtoJson(self.$mainArea);
            };
            this.collectRequiredCss = self._collectRequiredCss;
        },
        _collectRequiredCss: function ($requiredCssInput) {
            var self = this, conf = this.options, result = [];
            self.$mainArea.find('[data-role="css-require"]').each(function() {
                var $input = $(this);
                var rqCSS = $input.val();
                var $parent = $input.parents(conf.elItem).first();
                if (rqCSS) {
                    rqCSS = JSON.parse(rqCSS);
                    $.each(rqCSS, function(i, file) {
                        var name = file.name, cond = file.condition;
                        var field = cond.field;
                        var $field = $parent.find('[data-name='+field+']').first();
                        var fieldValue = $field.val();
                        var operator = cond.operator;
                        var value = cond.value;
                        if (eval('fieldValue' + operator + 'value')) {
                            if (result.indexOf(name) == -1) {
                                result.push(name);
                            }
                        }
                    });
                }
            });
            return result;
        },
        _elementHTMLtoJson: function($parent) {
            var self = this, conf = this.options;
            var pageData = [];
            $parent.children(conf.elItem).each(function() {
                var $elItem = $(this);
                if ($elItem.css('opacity') != 0) {
                    var data = {};
                    data.type = $elItem.data('itemtype');
                    data.settings = {};
                    var $settings = $elItem.children(conf.settingPanel);
                    $settings.find('[data-name]').each(function(id, el) {
                        var $field = $(this), name = $field.data('name');
                        if ($field.data('type') == 'multitext') {
                            var values = [];
                            var $multiContainer = $field.parents('[data-role=multitext-container]').first();
                            var $itemsWrap = $multiContainer.find('[data-role=multitext-items]');
                            $itemsWrap.find('[data-role=multitext-item]').each(function(i, el) {
                                var $item = $(this);
                                var val = {};
                                $item.find('[data-mname]').each(function(ii, mname) {
                                    val[$(this).data('mname')] = $(this).val();
                                });
                                values.push(val);
                            });
                            $field.val(JSON.stringify(values));
                        }
                        
                        data.settings[name] = $field.val();
                    });
                    
                    var $childWrap = $elItem.children(conf.elItemChildren);
                    if ($childWrap.length) {
                        if ($childWrap.find(conf.elItem).length) {
                            data.children = self._elementHTMLtoJson($childWrap);
                        }
                    }
                    pageData.push(data);
                }
            });
            return pageData;
        },
        _buildElementTypes: function() {
            var self = this, conf = this.options;
            $.each(this.itemTypes, function(i, type) {
                var $el = $(self.elTypeTmpl).tmpl(type);
                $el.appendTo(self.$eleArea);
                $.each(type.fields, function(j, field) {
                    var $field = $(self.elFieldTmpl).tmpl(field);
                    var $fields = $(conf.elItemFields, $el);
                    $field.appendTo($fields);
                });
            });
        },
        _convertItemTypes: function(itemTypeArray) {
            var itemTypeObject = {};
            $.each(itemTypeArray, function(i, type) {
                itemTypeObject[type.name] = type;
            });
            return itemTypeObject;
        },
        _loadData: function() {
            var self = this, conf = this.options;
            this._attacheElementNode(this.$mainArea, conf.itemData);
        },
        _attachHeader: function($field, $elItem) {
            $elTitle = $elItem.find('.el-title').first();
            $elTitle.find('.el-subtitle').remove();
            if ($field.data('type') == 'select') {
                $elTitle.append(' <span class="el-subtitle">' + $field.find('option[value="'+$field.val()+'"]').text() + '</span>');
            } else {
                $elTitle.append(' <span class="el-subtitle">' + $field.val() + '</span>');
            }
        },
        _attacheDesc: function($field, $elItem) {
            var self = this, conf = this.options;
            var $shortDesc = $elItem.find(conf.shortDesc).first();
            var txt = $field.val();
            $shortDesc.html(self._decodeImgUrl(txt));
        },
        _decodeImgUrl: function(content) {
            var self = this; conf = this.options;
            return content.gsub(/\{\{media(.*?)\}\}/i, function (match) {
                var src = '';
                match[0].gsub(/url=\"(.*?)\"/, function(url) {
                    src = conf.mediaUrl + url[1];
                });
                match[0].gsub(/url=\&quot;(.*?)\&quot;/, function(url) {
                    src = conf.mediaUrl + url[1];
                });
                return src;
            });
        },
        _addColunmClass: function($field, $elItem) {
            if (typeof $field.data('old_value') === 'undefined') {
                $elItem.removeClass('col-sm-24');
            } else {
                $elItem.removeClass('col-sm-' + $field.data('old_value'));
            }
            
            $field.data('old_value', $field.val());
            $elItem.addClass('col-sm-' + $field.val());
        },
        _bindItemEvents: function($cloneItem, $sourceItem) {
            var self = this, conf = this.options;
            $cloneItem.sortable(self._sortableConfig());
            var $childWrap = $cloneItem.find(conf.elItemChildren);
            if ($childWrap.length) {
                $childWrap.sortable(self._sortableConfig());
            }
            $cloneItem.find('[data-role=multitext-items]').sortable({handle: '.item-icon'});
            $cloneItem.find('.ui-resizable-e').remove();
            self._addResizableEvent($cloneItem);
            $cloneItem.find(conf.elItem).each(function() {
                self._addResizableEvent($(this));
            });
            self.$mainArea.sortable('refresh');
            self.$mainArea.sortable('refreshPositions');
            if (typeof $sourceItem != 'undefined') {
                $('select', $cloneItem).each(function(i, el) {
                    $(this).val($('select:eq(' + i + ')', $sourceItem).val()).trigger('change');
                });
            }
        },
        _bindEvents: function() {
            var self = this, conf = this.options;
            self.toolbarEvents = function() {
                function toggleSettingPanel($elItem, $settingPanel) {
                    $(conf.settingPanel).each(function() {
                        if(!($(this).is($settingPanel))){
                            $(this).hide().removeClass('active');
                        }
                    });
                    $settingPanel.fadeToggle(100, 'linear', function() {
                        $settingPanel.toggleClass('active');
                        if (!$settingPanel.hasClass('active')) {
                            if ($settingPanel.find('[data-attache_desc=true]').length) {
                                var $field = $settingPanel.find('[data-attache_desc=true]').first();
                                self._attacheDesc($field, $elItem);
                            }
                            if ($settingPanel.find('[data-attache_header=true]').length) {
                                var $title = $settingPanel.find('[data-attache_header=true]').first();
                                self._attachHeader($title, $elItem);
                            }
                        }
                    });
                };
                $(document).keyup(function(e) {
                    if (e.keyCode == 27 && (!($('body').hasClass('_has-modal') || $('body').hasClass('cdz-popup')))) {
                        var $settingPanel = $(conf.settingPanel.replace(' ','') + '.active', self.$mainArea).first();
                        if ($settingPanel.length) {
                            var $elItem = $settingPanel.parents(conf.elItem).first();
                            toggleSettingPanel($elItem, $settingPanel);
                        }
                    }
                });
                self.element.on('click', conf.settingBtn, function() {
                    var $btn = $(this), $elItem = $btn.parents(conf.elItem).first();                    
                    var $settingPanel = $(conf.settingPanel, $elItem).first();
                    toggleSettingPanel($elItem, $settingPanel);
                });
                self.element.on('click', conf.deleteBtn, function() {
                    var allow = confirm("Are you sure to delete it?");
                    if (allow) {
                        var $btn = $(this), $elItem = $btn.parents(conf.elItem).first();
                        $elItem.fadeOut(300).remove();
                    }
                });
                self.element.on('click', conf.closeBtn, function() {
                    var $btn = $(this), $elItem = $btn.parents(conf.elItem).first();
                    $elItem.find(conf.settingBtn).first().click();
                });
                self.element.on('click', conf.copyBtn, function() {
                    var $btn = $(this), $elItem = $btn.parents(conf.elItem).first();
                    var $cloneItem = $elItem.clone(false);
                    $cloneItem.insertAfter($elItem);
                    setTimeout(function() {
                        self._bindItemEvents($cloneItem, $elItem);
                    }, 50);
                });
                self.element.on('click', conf.addToPageBtn, function() {
                    var $btn = $(this), $elItem = $btn.parents(conf.elItem).first();
                    $cloneItem = $elItem.clone();
                    $cloneItem.find(conf.settingPanel).hide().removeClass('active');
                    $cloneItem.appendTo(self.$mainArea).removeClass(conf.inactiveClass);
                    setTimeout(function() {
                        self._bindItemEvents($cloneItem, $elItem);
                    }, 50);
                });
                self.element.on('click', conf.nextBtn, function() {
                    var $btn = $(this), $elItem = $btn.parents(conf.elItem).first();
                    if($elItem.next().length) {
                        $elItem.insertAfter($elItem.next());
                        $elItem.hide().fadeIn(400);
                    }
                });
                self.element.on('click', conf.prevBtn, function() {
                    var $btn = $(this), $elItem = $btn.parents(conf.elItem).first();
                    if($elItem.prev().length) {
                        $elItem.insertBefore($elItem.prev());
                        $elItem.hide().fadeIn(400);
                    }
                });
                
                self.element.on('click', '.multitext-toolbar [data-role=el-add-new-item]', function() {
                    var $btn = $(this), $multiContainer = $btn.parents('[data-role=multitext-container]').first();
                    var $itemsWrap = $multiContainer.find('[data-role=multitext-items]');
                    var $newItem = $itemsWrap.children().first().clone();
                    $newItem.find('[data-mname]').val('');
                    $newItem.find('[data-role=image-preview]').attr('src', conf.imagePlaceholder);
                    $newItem.find('.item-icon .m-image-preview').remove();
                    $newItem.find('.item-icon i').show();
                    $newItem.find('.item-icon').css({padding: ''});
                    $newItem.appendTo($itemsWrap).hide().fadeIn(400);
                });
                self.element.on('click', '.multitext-item [data-role=mt-open-item]', function() {
                    var $btn = $(this), $item = $btn.parents('[data-role=multitext-item]').first();
                    $multiContainer = $btn.parents('[data-role=multitext-container]').first();
                    $itemContent = $item.find('[data-role=mt-content]').first();
                    $('body').addClass('cdz-popup').css({paddingRight: self._getScrollBarWidth()});
                    $itemContent.fadeIn(400);
                });
                self.element.on('click', '.multitext-item [data-role=mt-close-item]', function() {
                    var $btn = $(this), $item = $btn.parents('[data-role=multitext-item]').first(),
                    $itemContent = $item.find('[data-role=mt-content]').first();
                    $itemContent.fadeOut(400).css({paddingRight: ''});
                    $('body').removeClass('cdz-popup').css({paddingRight: ''});
                    var $previewImage = $itemContent.find('[data-role=image-preview]');
                    var $item = $btn.parents('[data-role=multitext-item]').first();
                    var $icon = $item.find('.item-icon').first();
                    if ($previewImage.length) {
                        $icon.find('.m-image-preview').remove();
                        $icon.append('<img class="m-image-preview" src="' + $previewImage.attr('src') + '" >');
                        $icon.find('i.item-sign').hide();
                        $icon.css({padding: '0 0'});
                    } else {
                        $icon.find('i.item-sign').show();
                        $icon.css({padding: ''});
                    }
                });
                self.element.on('click', '.multitext-item [data-role=mt-remove-item]', function() {
                    var $btn = $(this),
                    $multiContainer = $btn.parents('[data-role=multitext-container]').first(),
                    $itemsWrap = $multiContainer.find('[data-role=multitext-items]');
                    
                    var allow = confirm("Are you sure to delete it?");
                    if (allow) {
                        var itemLength = $itemsWrap.find('[data-role=multitext-item]').length;
                        if (itemLength > 1) {
                            $item = $btn.parents('[data-role=multitext-item]').first()
                            $item.fadeOut(400, 'linear', function() {
                                $item.remove();
                                $('body').removeClass('cdz-popup').css({paddingRight: ''});
                            });
                        } else {
                            alert("Cannot delete this item because number of items must be larger than 1.");
                        }
                    }
                });
                self.element.on('change', '[data-type=image]', function() {
                    var $input = $(this), $parent = $input.parents('[data-role=image-container]').first();
                    if ($parent.length && $parent.find('[data-role=image-preview]').length) {
                        $parent.find('[data-role=image-preview]').first().attr('src', self._filterImage($input.val()));
                    }
                });
            };
            self.fieldEvents = function() {
                self.element.on('change', '.el-input[data-name=width]', function() {
                    var $field = $(this), $elItem = $field.parents(conf.elItem).first();
                    if ($elItem.data('itemtype') == 'col') {
                        self._addColunmClass($field, $elItem);
                        self._attachHeader($field, $elItem);
                    }
                });
                self.element.on('change', '.el-input[data-name=background]', function() {
                    var $field = $(this), $elItem = $field.parents(conf.elItem).first();
                    var value = $field.val();
                    var imageSrc = self._filterImage(value);
                    if (value) {
                        $elItem.css('background-image', 'url(' + imageSrc + ')');
                    } else {
                        $elItem.css('background-image', '');
                    }
                });
            };
            self.submitEvents = function() {
                self.element.on('click', conf.submitBtn, function() {
                    window.result = self._elementHTMLtoJson(self.$mainArea);
                });
            };
            self.toolbarEvents();
            self.fieldEvents();
            self.submitEvents();
        },
        _getScrollBarWidth: function () {
            var $outer = $('<div>').css({visibility: 'hidden', width: 100, overflow: 'scroll'}).appendTo('body'),
                widthWithScroll = $('<div>').css({width: '100%'}).appendTo($outer).outerWidth();
            $outer.remove();
            return 100 - widthWithScroll;
        },
        _droppableConfig: function() {
            var self = this, conf = this.options;
            return {
                accept: conf.elItem,
                drop: function(event, ui) {
                    //self.$currentDrop = $(this);
                }                
            }
        },
        _sortableConfig: function(){
            var self = this, conf = this.options;
            return {
                handle: conf.sortHandle,
                items: ' > ' + conf.elItem,
                placeholder: 'ui-state-highlight',
                connectWith: conf.elItemChildren + ',' + conf.mainArea,
                start: function(event, ui) {
                    var $item = ui.item;
                    ui.item.children(conf.elItemChildren).hide();
                    ui.item.children(conf.settingPanel).hide();
                    ui.item.children(conf.shortDesc).hide();
                                        
                    var $header = $item.children('.el-item-header');
                    $header.find('.header-actions').hide();
                    $header.find('.el-item-handle').css({width: '100%', maxWidth: '100%', border: 'none'});
                    
                    var height = $header.outerHeight(), width = $header.width();
                    ui.placeholder.css({
                        minWidth: 20,
                        width: 20,
                        maxWidth: '100%',
                        height: height,
                        width: $item.width(),
                        overflow: 'hidden',
                        float: 'left'
                    });
                    $item.css({
                        height: height,
                        width: 300,
                        overflow: 'hidden',
                        top: 0
                    });
                    var uiSortableData = $(this).data('uiSortable');
                    uiSortableData.offset.click.top = 10;
                    uiSortableData.offset.click.left = 20;
                    
                    this.$stayElement = $item.clone().css({opacity: 0, position: 'absolute'}).insertBefore($item);
                    this.$originalParent = $item.parent();
                    
                    $(this).sortable('refresh');
                    $(this).sortable('refreshPositions');
                },
                change: function(event, ui) {
                    
                },
                stop: function(event, ui) {
                    this.$stayElement.remove();
                    var $item = ui.item;
                    $item.css({width:'', height:'', right:'', bottom:'', top:'', overflow: '', left: '', position: ''});
                    ui.placeholder.css({width:'', height:'', right:'', bottom:'', top:'', overflow: '', left: '', position: '', minWidth: '', maxWidth: ''});
                    ui.item.children(conf.elItemChildren).css({display: ''});
                    ui.item.children(conf.settingPanel).css({display: ''});
                    ui.item.children(conf.shortDesc).css({display: ''});
                    $item.children(conf.sortHandle).removeAttr('style');
                    
                    var $header = $item.children('.el-item-header');
                    $header.find('.header-actions').css({display: ''});
                    $header.find('.el-item-handle').css({width: '', maxWidth: '', border: ''});
                    if (self.$currentDrop != false) {
                        if (!self.$currentDrop.is(this.$originalParent)) {
                            $item.appendTo(self.$currentDrop);
                        }
                    }
                    $item.hide().fadeIn(300);
                    self.$currentDrop = false;
                    $(this).sortable('refresh');
                    $(this).sortable('refreshPositions');
                }
            }
        },
        _addResizableEvent: function($elItem) {
            if ($elItem.data('itemtype') == 'col') {
                $elItem.resizable({
                    containment: 'parent',
                    handles: 'e',
                    resize: function(event, ui) {
                        var width = ui.size.width;
                        var parentWidth = $elItem.parent().width();
                        var col = Math.min(24, Math.max(1, Math.ceil(width/(parentWidth/24))));
                        $elItem.find('.el-title .el-subtitle').first().text('col-sm-' + col);
                    },
                    stop: function(event, ui) {
                        var orgSize = ui.originalSize;
                        var width = ui.size.width;
                        var parentWidth = $elItem.parent().width();
                        var col = Math.min(24, Math.max(1, Math.ceil(width/(parentWidth/24))));
                        $elItem.find('[data-name="width"]').first().val(col).trigger('change');
                        $elItem.css({'width': '', 'height': ''});
                        ui.size.width = $elItem.width();
                    }
                });
            }
        },
        _attacheElementNode: function($parent, data) {
            var self = this, conf = this.options;
            $.each(data, function(i, item) {
                var type = item.type;
                var itemType = self.itemTypesObject[type];
                var $elItem = $(self.elTypeTmpl).tmpl(itemType);
                $elItem.removeClass(conf.inactiveClass);
                $elItem.appendTo($parent);
                
                self._addResizableEvent($elItem);
                
                $.each(itemType.fields, function(j, field) {
                    var $field = $(self.elFieldTmpl).tmpl(field);
                    var $fields = $(conf.elItemFields, $elItem);
                    $field.appendTo($fields);
                });
                
                var settings = item.settings, $settingPanel = $(conf.settingPanel, $elItem).first();
                $.each(settings, function(name, value) {
                    $field = $settingPanel.find('[data-name='+ name +']');
                    $field.val(value).trigger('change');
                    if ($field.data('attache_header') === true) {
                        self._attachHeader($field, $elItem);
                    }
                    if ($field.data('attache_desc') === true) {
                        self._attacheDesc($field, $elItem);
                    }
                    if (name === 'width') {
                        self._addColunmClass($field, $elItem);
                    }
                    if ($field.data('type') === 'image') {
                        var imageSrc = self._filterImage(value);
                        $field.parents('[data-role=image-container]').find('[data-role=image-preview]').attr('src', imageSrc);
                        if ((name === 'background') && value) {
                            $elItem.css('background-image', 'url(' + imageSrc + ')');
                        }
                    }
                    
                    if ($field.data('type') === 'multitext') {
                        var values = $field.val();
                        
                        if (values) {
                            values = JSON.parse(value);
                            var $multiContainer = $field.parents('[data-role=multitext-container]').first();
                            var $itemsWrap = $multiContainer.find('[data-role=multitext-items]');
                            var $firstItem = $itemsWrap.find('[data-role=multitext-item]').first();
                            $itemsWrap.sortable({
                                handle: '.item-icon'
                            });
                            $.each(values, function(i, value) {
                                var $newItem = $firstItem;
                                if (i > 0) {
                                    $newItem = $firstItem.clone().appendTo($itemsWrap);
                                }
                                $newItem.find('[data-mname]').each(function(ii, mname) {
                                    var $mField = $(this);
                                    var mname = $mField.data('mname');
                                    $(this).val(value[mname]);
                                    if($mField.data('type') == 'image') {
                                        var imageSrc = self._filterImage(value[mname]);
                                        $mField.parents('[data-role=image-container]').find('[data-role=image-preview]').attr('src', imageSrc);
                                    }
                                    
                                    var $previewImage = $newItem.find('[data-role=image-preview]');
                                    var $icon = $newItem.find('.item-icon').first();
                                    if ($previewImage.length) {
                                        $icon.find('.m-image-preview').remove();
                                        $icon.append('<img class="m-image-preview" src="' + $previewImage.attr('src') + '" >');
                                        $icon.find('i.item-sign').hide();
                                        $icon.css({padding: '0 0'});
                                    } else {
                                        $icon.find('i.item-sign').show();
                                        $icon.css({padding: ''});
                                    }
                                });
                            });
                        }
                    }
                });
                
                var $childWrap = $elItem.children(conf.elItemChildren);
                if ($childWrap.length) {
                    $childWrap.sortable(self._sortableConfig());
                    //$childWrap.droppable(self._droppableConfig());
                }
                if ((typeof item.children !== 'undefined') && item.children.length) {
                    self.$mainArea.sortable('refresh');
                    self.$mainArea.sortable('refreshPositions');
                    setTimeout(function() {
                        self._attacheElementNode($childWrap, item.children);
                    },50);
                }
            });
        },
        _filterImage: function(value){
            var self = this, conf = this.options;
            if( (typeof value !== 'undefined') && (value != '')){
                var patt = new RegExp('url="\\S+"','g');
                var patt2 = new RegExp('url=&quot;\\S+&quot;');
                if (patt.test(value)) {
                    var src = conf.mediaUrl + (value).match(patt)[0].replace(/"/g,'').replace(/url=/,"");
                } else if (patt2.test(value)) {
                    var src = conf.mediaUrl + (value).match(patt)[0].replace(/&quot;/g,'').replace(/url=/,"");
                } else {
                    var src = value;
                }
            }else{
                var src = conf.imagePlaceholder;	
            }
            return src;
        },
        _buildDropDrag: function() {
            var self = this, conf = this.options;
            self.$mainArea.sortable(self._sortableConfig());
            //self.$mainArea.droppable(self._droppableConfig());
            $(conf.elItem, this.$eleArea).draggable({
                handle: conf.sortHandle,
                connectToSortable: self.$mainArea,
                helper: "clone",
                revert: "invalid",
                stop: function(event, ui) {
                    var $newEl = self.$mainArea.find('.' + conf.inactiveClass);
                    $newEl.css({width:'', height:'', right:'', bottom:'', top:''});
                    $newEl.removeClass(conf.inactiveClass);
                    $newEl.removeClass('ui-draggable ui-draggable-handle');
                    var $childWrap = $newEl.children(conf.elItemChildren);
                    self._bindItemEvents($newEl);
                    if (ui.helper.hasClass(conf.inactiveClass)) {
                        $newEl.remove();
                    }
               }
            });
        }
    });
})(jQuery);