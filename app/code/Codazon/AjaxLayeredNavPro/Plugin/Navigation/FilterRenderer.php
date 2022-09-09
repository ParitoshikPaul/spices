<?php
/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Codazon\AjaxLayeredNavPro\Plugin\Navigation;

class FilterRenderer 
{
    protected $helper;
    
    protected $layout;
    
    protected $block = \Magento\LayeredNavigation\Block\Navigation\FilterRenderer::class;
    
    protected $swatchBlock = \Magento\Swatches\Block\LayeredNavigation\RenderLayered::class;
    
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Codazon\AjaxLayeredNavPro\Helper\Data $helper
    ) {
        $this->layout = $layout;
        $this->helper = $helper;
    }
    
    public function aroundRender(
        \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
    ) {
        if (!$this->helper->getLayout()->getBlock('category.products.list.container')) {
            return $proceed($filter);
        }
        if ($filter->hasAttributeModel() && $this->helper->enableAjaxLayeredNavigation()) {
            $attributeModel = $filter->getAttributeModel();
            $this->helper->extractExtraOptions($attributeModel);
            if ($customStyle = $attributeModel->getData('custom_style')) {
                if ($this->helper->enableMultiSelect()) {
                    if (($customStyle === 'checkbox') && ($attributeModel->getFrontendInput() === 'price')) {
                        return $proceed($filter);
                    }
                } else {
                    if (($customStyle === 'checkbox') || (($customStyle === 'slider') && ($attributeModel->getFrontendInput() != 'price'))) {
                        return $proceed($filter);
                    }
                }
                return $this->helper->getFilterHtml($filter, $customStyle);
            } else {
                return $proceed($filter);
            }
        } elseif ($filter instanceof \Magento\CatalogSearch\Model\Layer\Filter\Category 
            && $this->helper->enableCategoryMultiSelect()) {
            $customStyle = 'checkbox';
            return $this->helper->getLayout()->createBlock($this->block)->setTemplate('Codazon_AjaxLayeredNavPro::layer/custom-style/'.$customStyle.'.phtml')->setOptionsFilter($filter)
                ->setSwatchFilter($filter)
                ->setIsSwatchAttribute(false)
                ->setData('custom_style', $customStyle)
                ->toHtml();
        } elseif ($this->helper->isRatingFilter($filter)) {
            return $this->helper->getFilterByRatingHtml($filter);
        } elseif ($this->helper->isStockStatusFilter($filter)) {
            if ($this->helper->enableMultiSelect()) {
                return $this->helper->getFilterHtmlWithCustomStyle($filter, 'checkbox');
            } else {
                return $proceed($filter);
            }
        }
        return $proceed($filter);
    }
    
    
}