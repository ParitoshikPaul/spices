<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Block\Catalog;

use Codazon\ThemeLayoutPro\Block\Catalog\FileInfo;

class Subcategories extends \Magento\Framework\View\Element\Template {
     
    protected $categoryHelper;
    
    protected $coreRegistry;
    
    protected $helper;
    
    protected $configData;
    
    protected $fileInfo;
    
    protected $attributeThumbnail = 'cdz_thumbnail_image';
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Codazon\ThemeLayoutPro\Helper\Data $helper,
        array $data = []
    ) {
        $this->categoryHelper = $categoryHelper;
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }
    
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }
    
    public function getCurrentSubcategories()
    {
        if (!$this->hasData('current_subcategories')) {
            $category = $this->getCurrentCategory();
            $subcatgeories = $this->helper->createObject(\Magento\Catalog\Model\ResourceModel\Category\Collection::class, [])
                ->addAttributeToSelect(
                    'url_key'
                )->addAttributeToSelect(
                    'name'
                )->addAttributeToSelect(
                    'all_children'
                )->addAttributeToSelect(
                    'is_anchor'
                )->addAttributeToFilter(
                    'is_active',
                    1
                )->addAttributeToFilter(
                    'cdz_thumbnail_exclude',
                    0
                )->addIdFilter(
                    $category->getChildren()
                )->setOrder(
                    'position',
                    \Magento\Framework\DB\Select::SQL_ASC
                )->addAttributeToSelect($this->attributeThumbnail)
                ->joinUrlRewrite();
            $this->setData('current_subcategories', $subcatgeories);
        }
        return $this->getData('current_subcategories');
    }
    
    public function getThumbnailUrl($category)
    {
        return $this->getImageUrl($category, $this->attributeThumbnail);
    }
    
    public function getFileInfoObject()
    {
        if ($this->fileInfo === null) {
            $this->fileInfo = $this->helper->getObject(FileInfo::class);
        }
        return $this->fileInfo;
    }
    
    public function getImageUrl($category, $attributeCode = 'image')
    {
        $url = false;
        $image = $category->getData($attributeCode);
        if ($image) {
            $fileInfo = $this->getFileInfoObject();
            if (is_string($image)) {
                $mediaBaseUrl = $fileInfo->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                );
                if ($fileInfo->isBeginsWithMediaDirectoryPath($image)) {
                    $relativePath = $fileInfo->getRelativePathToMediaDirectory($image);
                    $url = rtrim($mediaBaseUrl, '/') . '/' . ltrim($relativePath, '/');
                } elseif (substr($image, 0, 1) !== '/') {
                    $url = rtrim($mediaBaseUrl, '/') . '/' . ltrim(FileInfo::ENTITY_MEDIA_PATH, '/') . '/' . $image;
                } else {
                    $url = $image;
                }
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }   
    
    public function getCurrentSubcategoriesData()
    {
        $subcatgeories = $this->getCurrentSubcategories();
        $data = [];
        if ($subcatgeories) {
            foreach ($subcatgeories as $subcat) {
                $data[]  = [
                    'id'        => $subcat->getId(),
                    'name'      => $subcat->getName(),
                    'url'       => $this->categoryHelper->getCategoryUrl($subcat),
                    'thumbnail' => $this->getThumbnailUrl($subcat),
                ];
            }
        }
        return $data;
    }
    
    public function getSliderData($configData)
    {
        $sliderData = [
            'nav'           => $configData['slider_nav'],
            'dots'          => $configData['slider_dots'],
            'loop'          => $configData['slider_loop'],
            'lazyLoad'      => true,
            'margin'        => $configData['slider_margin'],
            'autoplay'      => $configData['slider_autoplay'],
            'autoplayTimeout' => $configData['slider_autoplay_timeout']
        ];
        $adapts = ['1900', '1600', '1420', '1280','980','768','480','320','0'];
        foreach ($adapts as $adapt) {
             $sliderData['responsive'][$adapt] = ['items' => $configData['items_' . $adapt]];
        }
        return $sliderData;
    }
    
    public function getGridData($configData)
    {
        $adapts = ['1900', '1600', '1420', '1280','980','768','480','320','0'];
        $itemPerRow = [];
        foreach ($adapts as $adapt) {
            $itemPerRow[$adapt] = (float)$configData['items_' . $adapt];
        }
        return $itemPerRow;
    }
    
    public function canShowBlock()
    {
        return ((bool)$this->helper->getConfig('pages/category_view/subcategories/enable')) 
            && ((bool)$this->getCurrentCategory()->getData('cdz_thumbnail_enable'));
    }
    
    public function getConfigData()
    {
        if ($this->configData === null) {
            $this->configData = [
                'title'             => $this->helper->getConfig('pages/category_view/subcategories/title'),
                'show_slider'       => (bool)$this->helper->getConfig('pages/category_view/subcategories/show_slider'),
                'thumbnail_width'   => (float)$this->helper->getConfig('pages/category_view/subcategories/thumbnail_width'),
                'thumbnail_height'  => (float)$this->helper->getConfig('pages/category_view/subcategories/thumbnail_height'),
                'items_1900'    => (float)$this->helper->getConfig('pages/category_view/subcategories/items_1900'),
                'items_1600'    => (float)$this->helper->getConfig('pages/category_view/subcategories/items_1600'),
                'items_1420'    => (float)$this->helper->getConfig('pages/category_view/subcategories/items_1420'),
                'items_1280'    => (float)$this->helper->getConfig('pages/category_view/subcategories/items_1280'),
                'items_980'     => (float)$this->helper->getConfig('pages/category_view/subcategories/items_980'),
                'items_768'     => (float)$this->helper->getConfig('pages/category_view/subcategories/items_768'),
                'items_480'     => (float)$this->helper->getConfig('pages/category_view/subcategories/items_480'),
                'items_320'     => (float)$this->helper->getConfig('pages/category_view/subcategories/items_320'),
                'items_0'       => (float)$this->helper->getConfig('pages/category_view/subcategories/items_0'),
                'slider_loop'   => (bool)$this->helper->getConfig('pages/category_view/subcategories/slider_loop'),
                'slider_nav'    => (bool)$this->helper->getConfig('pages/category_view/subcategories/slider_nav'),
                'slider_dots'   => (bool)$this->helper->getConfig('pages/category_view/subcategories/slider_dots'),
                'slider_autoplay'           => (bool)$this->helper->getConfig('pages/category_view/subcategories/slider_autoplay'),
                'slider_autoplay_timeout'   => (float)$this->helper->getConfig('pages/category_view/subcategories/slider_autoplay_timeout'),
                'slider_margin' => (float)$this->helper->getConfig('pages/category_view/subcategories/slider_margin'),
                'style'         => $this->helper->getConfig('pages/category_view/subcategories/style'),
                'placeholder'   => $this->helper->getMediaUrl('codazon/subcategories/placeholder/' . $this->helper->getConfig('pages/category_view/subcategories/placeholder')),
                'hovered_style' => (bool)$this->helper->getConfig('pages/category_view/subcategories/hovered_style')
            ];
            if ($category = $this->getCurrentCategory()) {
                if (!$category->getIsAnchor()) {
                     $this->configData['show_slider'] = (bool)$this->helper->getConfig('pages/category_view/subcategories/show_slider_non_anchor');
                }
            }
        }
        return $this->configData;
    }
}