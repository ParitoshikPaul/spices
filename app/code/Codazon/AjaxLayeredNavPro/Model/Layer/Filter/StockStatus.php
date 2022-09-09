<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\AjaxLayeredNavPro\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use \Magento\Framework\App\ObjectManager;
use \Codazon\AjaxLayeredNavPro\Helper\Data as LayerHelper;
/**
 * Layer attribute filter
 */
class StockStatus extends AbstractFilter
{   
    protected $objectManager;
    
    protected $helper;
    
    protected $enableMultiSelect;
    
    protected $stockHelper;
    
    protected $stockStatus = [
        1 => 'In Stock',
        0 => 'Out of Stock'
    ];
    
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->objectManager = ObjectManager::getInstance();
        $this->helper = $this->objectManager->get(LayerHelper::class);
        //$this->stockHelper = $this->objectManager->get('Magento\CatalogInventory\Helper\Stock');
        $this->enableMultiSelect = $this->helper->enableMultiSelect();
        $this->enableStockFilter = $this->helper->enableFilterByStockStatus();
        $this->_requestVar = LayerHelper::STOCK_STATUS_CODE;
    }
    
    public function getName()
    {
        return $this->helper->getConfig(LayerHelper::STOCK_FILTER_LABEL) ? : __('Stock Status');
    }
    
    public function applyToCollection($productCollection, $request, $requestVar)
    {
        $attributeValue = $request->getParam($requestVar, false);
        if (($attributeValue === '1') || ($attributeValue === '0')) {
            $this->addStockFilterToCollection($productCollection, (int)$attributeValue);
        }
        return $productCollection;
    }
    
    public function addStockFilterToCollection($productCollection, $status)
    {
        if (!$productCollection->getFlag($this->_requestVar . '_filtered')) {
            if ($this->helper->isMagentoUp24()) {
                if (!$productCollection->getFlag($this->_requestVar . '_filtered')) {
                    $productCollection->addFieldToFilter($this->_requestVar, $status);
                    $productCollection->setFlag($this->_requestVar . '_filtered', 1);
                }
            } else {
                    $manageStock = $this->helper->getConfig(
                        \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                    if (is_array($status)) {
                        $status = implode(',', $status);
                    }
                    $cond = [
                        '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock in (' . $status . ')',
                        '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0'
                    ];

                    if ($manageStock) {
                        $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock in (' . $status . ')';
                    } else {
                        $cond[] = '{{table}}.use_config_manage_stock = 1';
                    }

                    $productCollection->joinField(
                        'inventory_in_stock',
                        'cataloginventory_stock_item',
                        'is_in_stock',
                        'product_id=entity_id',
                        '(' . join(') OR (', $cond) . ')'
                    );
                    $productCollection->getSelect()->where('is_in_stock in (' . $status . ')');
            }
            $productCollection->setFlag($this->_requestVar . '_filtered', 1);
        }
    }
    
    protected function normalizeFacetedData($items) {
        $options = [];
        for ($i = 1; $i >= 0; $i--) {
            $item = isset($items[$i]) ? $items[$i] : ['value' => $i, 'count' => 0];
            $item['label'] = $this->_getStockLabel($item['value']);
            $options[] = $item;
        }
        return $options;
    }
    
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar, null);
        $productCollection = $this->getLayer()->getProductCollection();
       
        if (($attributeValue === null) || (($attributeValue != 1) && ($attributeValue != 0))) {
            return $this;
        }
        if ($this->enableMultiSelect) {
            if ($this->helper->isMagentoUp24()) {
                $this->setBeforeApplyFacetedData(
                    $this->normalizeFacetedData($this->helper->getBeforeApplyFacetedData($productCollection, null, null, $this->_requestVar))
                );
            } else {
                $this->setBeforeApplyFacetedData($this->_getStockData($productCollection));
            }
        }
        $this->setData('skip_seo', true);
        $productCollection = $this->getLayer()->getProductCollection();
        $attributeValue = explode(',', $attributeValue);
        $this->addStockFilterToCollection($productCollection, $attributeValue);
        $label = $this->_getStockLabel($attributeValue);
        $this->getLayer()
            ->getState()
            ->addFilter($this->_createItem($label, $attributeValue));
        if (!$this->enableMultiSelect) {
            $this->_items = [];
        }
        
        return $this;
    }
    
    
    protected function _getStockLabel($attributeValues)
    {
        if (is_array($attributeValues)) {
            $labels = [];
            foreach ($attributeValues as $attributeValue) {
                $labels[] = __($this->stockStatus[$attributeValue]);
            }
            return $labels;
        } else {
             return __($this->stockStatus[$attributeValues]);
        }
    }
    
    

    protected function _getStockData($productCollection)
    {
        if ($this->helper->isMagentoUp24()) {
            try {
                if ($this->getBeforeApplyFacetedData()) {
                    return $this->getBeforeApplyFacetedData();
                } else {
                    return $this->normalizeFacetedData($productCollection->getFacetedData($this->_requestVar));
                }
            } catch (\Magento\Framework\Exception\StateException $e) {
                return [];
            }
        } else {
            $connection = $productCollection->getConnection();
            $options = [];
            $cloneCollection = [];
            $cloneCollection[] = clone $productCollection;
            $cloneCollection[] = clone $productCollection;
            for ($i = 1; $i >= 0; $i--) {
                $col = $cloneCollection[$i];
                $this->addStockFilterToCollection($col, $i);
                $options[] = [
                    'label' => $this->_getStockLabel($i),
                    'value' => $i,
                    'count' => $connection->fetchOne($col->getSelectCountSql())
                ];
            }
            return $options;
        }
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
     protected function _getItemsData()
     {
        $data = [];
        $productCollection = $this->getLayer()->getProductCollection();
        if ($data = $this->getBeforeApplyFacetedData()) {
        } else {
            $data = $this->_getStockData($productCollection);
        }
        $this->setData('items_data', $data);
        return $data;
    }
}
