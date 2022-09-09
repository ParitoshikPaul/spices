<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Block;

use Magento\Customer\Model\Context;

class MainContent extends LayoutAbstract
{
    
    protected $_mainContentModel = false;
    protected $_mainContent = false;
    
    public function _construct()
    {
        parent::_construct();
        $this->setNeedFilterHtml(true);
        $this->setTemplate('Codazon_ThemeLayoutPro::content-builder/wrapper.phtml');
        return $this;
    }
    
    public function getMainContent()
    {
        if ($this->_mainContentModel === false) {
            $this->_mainContentModel = $this->helper->getMainContent();
        }
        return $this->_mainContentModel;
    }
    
    public function getMainContentElements()
    {
        if ($this->_mainContent === false) {
            $mainContent = $this->getMainContent()->getData('themelayout_content');
            $this->_mainContent = json_decode((string)$mainContent, true);
        }
        return $this->_mainContent;
    }
    
    public function getElementHtml($elements)
    {
        $html = '';
        $this->setNeedFilterHtml(false);
        foreach($elements as $element) {
            $this->setData('item_data', $element);
            $this->setTemplate('Codazon_ThemeLayoutPro::content-builder/element.phtml');
            $html .= $this->toHtml();
        }
        return $html;
    }
    
    protected function _toHtml()
    {
        if ($this->getNeedFilterHtml() === true) {
            return $this->filter(parent::_toHtml());
        } else {
            return parent::_toHtml();
        }
    }
    
    public function getElementCssClass($type, $settings) {
        $class = [];
        switch ($type) {
            case 'container':
                if ($settings['container_type'] == 'box') {
                    $class[] = 'container';
                } else {
                    $class[] = 'container-fluid';
                }
                if (!empty($settings['hide_on'])) {
                    $class[] = $settings['hide_on'];
                }
                
                $class[] = $settings['class'];
                break;
            case 'row':
                $class[] = 'row';
                $class[] = $settings['class'];
                if (!empty($settings['hide_on'])) {
                    $class[] = $settings['hide_on'];
                }
                break;
            case 'col':
                $class[] = 'col-sm-' . $settings['width'];
                $class[] = $settings['class'];
                if (!empty($settings['hide_on'])) {
                    $class[] = $settings['hide_on'];
                }
                break;
            case 'html':
                $class[] = 'widget block block-static-block';
                if (!empty($settings['hide_on'])) {
                    $class[] = $settings['hide_on'];
                }
                break;
            default:
                $class[] = $settings['class'];
                if (!empty($settings['hide_on'])) {
                    $class[] = $settings['hide_on'];
                }
        }
        return trim(implode(' ', $class));
    }
    
    public function getElementCssInline($type, $settings)
    {
        $style = [];
        /* if (!empty($settings['background'])) {
            $style[] = 'background-image: url(' . $settings['background'] . ')';
        } */
        if (!empty($settings['style'])) {
            $style[] = $settings['style'];
        }
        return implode(';', $style);
    }
    
    public function getImagesSliderHtml($settings)
    {
        $this->setNeedFilterHtml(false);
        $sliderData = new \Magento\Framework\DataObject();
        $sliderData->addData($settings);
        $this->setData('slider_data', $sliderData);
        if ($template = $sliderData->getData('custom_template')) {
            $this->setTemplate($template);
        } else {
            $this->setTemplate('Codazon_ThemeLayoutPro::content-builder/element/images-slider.phtml');
        }
        return $this->toHtml();
    }
    
    public function getHtmlSliderHtml($settings)
    {
        $this->setNeedFilterHtml(false);
        $sliderData = new \Magento\Framework\DataObject();
        $sliderData->addData($settings);
        $this->setData('slider_data', $sliderData);
        if ($template = $sliderData->getData('custom_template')) {
            $this->setTemplate($template);
        } else {
            $this->setTemplate('Codazon_ThemeLayoutPro::content-builder/element/html-slider.phtml');
        }
        return $this->toHtml();
    }
    
    public function getHtmlSlideshowHtml($settings)
    {
        $this->setNeedFilterHtml(false);
        $sliderData = new \Magento\Framework\DataObject();
        $sliderData->addData($settings);
        $this->setData('slider_data', $sliderData);
        $this->setTemplate('Codazon_ThemeLayoutPro::content-builder/element/slideshow.phtml');
        return $this->toHtml();
    }
    
    public function getTabsHtml($settings)
    {
        $this->setNeedFilterHtml(false);
        $tabsData = new \Magento\Framework\DataObject();
        $tabsData->addData($settings);
        $this->setData('tabs_data', $tabsData);
        if ($template = $tabsData->getData('custom_template')) {
            $this->setTemplate($template);
        } else {
            $this->setTemplate('Codazon_ThemeLayoutPro::content-builder/element/tabs.phtml');
        }
        return $this->toHtml();
    }
    
    public function getVideoHtml($settings)
    {
        $this->setNeedFilterHtml(false);
        $videoData = new \Magento\Framework\DataObject();
        $videoData->addData($settings);
        $this->setData('video_data', $videoData);
        $this->setTemplate('Codazon_ThemeLayoutPro::content-builder/element/video.phtml');
        return $this->toHtml();
    }
    
    public function getSlideshowSettings($slider)
    {
        $settings = [
            'items' => 1,
            'margin' => 0,
            'nav' => (bool)$slider->getData('show_nav'),
            'dots' => (bool)$slider->getData('show_dots'),
            'autoplay' => (bool)$slider->getData('auto_play'),
            'autoplayTimeout' => (float)$slider->getData('auto_play_timeout'),
            'lazyLoad' => true,
            'loop' => (bool)$slider->getData('loop')
        ];
        if ($slider->getData('animation_in')) {
            $settings['animateIn'] = $slider->getData('animation_in');
        }
        if ($slider->getData('animation_out')) {
            $settings['animateOut'] = $slider->getData('animation_out');
        }
        return $settings;
    }
    
    public function getCustomScript($settings)
    {
        return isset($settings['script']) ? $settings['script'] : '';
    }
}