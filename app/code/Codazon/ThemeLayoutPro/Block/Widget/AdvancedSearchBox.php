<?php
/**
 * Copyright © 2020 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Block\Widget;

use Magento\CatalogSearch\Model\Advanced;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;


class AdvancedSearchBox extends \Magento\CatalogSearch\Block\Advanced\Form implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'Codazon_ThemeLayoutPro::widget/advanced-search-box.phtml';
        
    protected $productFactory;
    
    protected $catalogSearchAdvanced;
    
    protected $attributeCollectionFactory;
    
    public function _prepareLayout()
    {
        return $this;
    }
    
    public function getProductFactory()
    {
        if ($this->productFactory === null) {
            $this->productFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(ProductFactory::class);
        }
        return $this->productFactory;
    }
    
    public function getAttributeCollectionFactory()
    {
        if ($this->attributeCollectionFactory === null) {
            $this->attributeCollectionFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(AttributeCollectionFactory::class);
        }
        return $this->attributeCollectionFactory;
    }
    
    
    public function getAttributesByCodes(array $codes)
    {
        $attributes = $this->getData('specified_attributes');
        if ($attributes === null) {
            $codesString = "'".implode("','", $codes)."'";
            $product = $this->getProductFactory()->create();
            $attributes = $this->getAttributeCollectionFactory()
                ->create()
                ->addHasOptionsFilter()
                ->addDisplayInAdvancedSearchFilter()
                ->setCodeFilter($codes)
                ->addStoreLabel($this->_storeManager->getStore()->getId());
                $attributes->getSelect()->order(new \Zend_Db_Expr("FIELD(main_table.attribute_code, $codesString)"));
                $attributes->load();
            foreach ($attributes as $attribute) {
                $attribute->setEntity($product->getResource());
            }
            $this->setData('specified_attributes', $attributes);
        }
        
        return $attributes;
    }
    
    public function getAllSearchableAttributes()
    {
        $attributes = $this->getData('all_searchable_attributes');
        if ($attributes === null) {
            $product = $this->getProductFactory()->create();
            $attributes = $this->getAttributeCollectionFactory()
                ->create()
                ->addHasOptionsFilter()
                ->addDisplayInAdvancedSearchFilter()
                ->addStoreLabel($this->_storeManager->getStore()->getId())
                ->setOrder('main_table.attribute_id', 'asc')
                ->load();
            foreach ($attributes as $attribute) {
                $attribute->setEntity($product->getResource());
            }
            $this->setData('all_searchable_attributes', $attributes);
        }
        return $attributes;
    }
    
    public function getInputAttributeCodes()
    {
        $codesString = $this->getData('attribute_codes');
        return trim($codesString) ? explode(',', $codesString) : [];
    }
    
    public function getDisplayedAttributes()
    {
        if ($codes = $this->getInputAttributeCodes()) {
            return $this->getAttributesByCodes($codes);
        } else {
            return $this->getAllSearchableAttributes();
        }
    }
    
    public function getSearchPostUrl()
    {
        return $this->getUrl('catalogsearch/advanced/result');
    }
    
    public function getAttributeSelectElement($attribute)
    {
        $useMultiSelect = $this->getData('use_multi_select');
        $extra = '';
        $options = $attribute->getSource()->getAllOptions(false);
        $name = $attribute->getAttributeCode();
        // 2 - avoid yes/no selects to be multiselects
        if (is_array($options) && count($options) > 2) {
            if ($useMultiSelect) {
                $extra .= 'multiple="multiple" size="4"';
                $name .= '[]';
            } else {
                $options = array_merge([[
                    'value' => '', 'label' => __('All')
                ]], $options);
            }
            
        } else {
            array_unshift($options, ['value' => '', 'label' => __('All')]);
        }       
        return $this->_getSelectBlock()->setName(
            $name
        )->setId(
            $attribute->getAttributeCode()
        )->setTitle(
            $this->getAttributeLabel($attribute)
        )->setExtraParams(
            $extra
        )->setValue(
            $this->getAttributeValue($attribute)
        )->setOptions(
            $options
        )->setClass(
            $useMultiSelect ? 'multiselect nice-scroll' : 'js-cdz-select'
        )->getHtml();
    }
}