<?php

/**
 * Copyright © 2020 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Block\Widget;

class BlogPostList extends \Magefan\Blog\Block\Widget\PostList implements \Magento\Widget\Block\BlockInterface
{
    protected $_isFullHtml;
    
    protected $_themeHelper;
    
    protected $_show;
    
    protected $_sliderData;
    
    protected function _beforeToHtml() {
        return $this;
    }
    
    public function getTemplate()
    {
        if ($this->isFullHtml()) {
            $template = $this->getData('post_template');
            if (($template == 'custom') && ($customTemplate = $this->getData('custom_template'))) {
                return (stripos($customTemplate, ':') === false) ? 'Magefan_Blog::' . $customTemplate : $customTemplate;
            } else {
                return 'Magefan_Blog::' . $template;
            }
        } else {
            if ($this->getData('main_opt_id')) {
                return 'Codazon_ThemeLayoutPro::widget/main-block-widget.phtml';
            } else {
                return 'Magefan_Blog::post/widget/ajax-blog.phtml';
            }
        }
    }
    
    public function isFullHtml()
    {
        if ($this->_isFullHtml === null) {
            $ajaxBlog = $this->getThemeHelper()->getConfig('pages/blog/use_ajax_blog');
            $this->_isFullHtml = ($this->getData('full_html')) || (!$ajaxBlog) || ($this->getData('ajax_load') === '0');
        }
        return $this->_isFullHtml;
    }
    
    public function getFilterData()
    {
        $data = $this->getData();
        unset($data['type']);
        unset($data['module_name']);
        return $data;
    }
    
    public function getThemeHelper()
    {
        if ($this->_themeHelper === null) {
            $this->_themeHelper = \Magento\Framework\App\ObjectManager::getInstance()->get('Codazon\ThemeLayoutPro\Helper\Data');
        }
        return $this->_themeHelper;
    }
    
    public function getSliderData()
    {
        if (!$this->_sliderData) {
            $this->_sliderData = [
                'nav'  => (bool)$this->getData('slider_nav'),
                'dots' => (bool)$this->getData('slider_dots')
            ];
            $adapts = ['1900','1600','1420','1280','980','768','480','320','0'];
            foreach ($adapts as $adapt) {
                 $this->_sliderData['responsive'][$adapt] = ['items' => (float)$this->getData('items_' . $adapt)];
            }
            $this->_sliderData['margin'] = (float)$this->getData('slider_margin');
        }
        return $this->_sliderData;
    }
    
    public function subString($str, $strLenght)
    {
        $str = $this->stripTags($str);
        if(strlen($str) > $strLenght) {
            $strCutTitle = substr($str, 0, $strLenght);
            $str = substr($strCutTitle, 0, strrpos($strCutTitle, ' '))."&hellip;";
        }
        return $str;
    }
    
    public function getElementShow()
    {
        if (!$this->_show) {
            $this->_show = explode(',', $this->getData('show_in_front'));
        }
        return $this->_show;
    }
    
    public function isShow($item)
    {
    	return in_array($item, $this->getElementShow());
    }
}