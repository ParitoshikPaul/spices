<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\AjaxLayeredNavPro\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use \Magento\Framework\App\ObjectManager;
/**
 * Layer attribute filter
 */
class Rating extends AbstractFilter
{   
    protected $objectManager;
    
    protected $helper;
    
    protected $enableMultiSelect;
    
    protected $sqlFieldName;
    
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
        $this->helper = $this->objectManager->get(\Codazon\AjaxLayeredNavPro\Helper\Data::class);
        $this->enableMultiSelect = $this->helper->enableMultiSelect();
        $this->_requestVar = $this->helper->getRatingCode();
        $this->sqlFieldName = $this->helper->getAvgRatingPercentFieldName();    
    }
    
    public function getName()
    {
        return $this->helper->getRatingFilterLabel();
    }
    
    public function applyToCollection($productCollection, $request, $requestVar)
    {
        $attributeValue = $request->getParam($requestVar);
        if ($attributeValue) {
            $percent = 100 * $attributeValue / 5;
            $this->helper->collectionRatingFilter($productCollection, $attributeValue);
        }
        return $productCollection;
    }
    
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        $productCollection = $this->getLayer()->getProductCollection();
       
        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $this;
        }
        $this->setData('skip_seo', true);
        $productCollection = $this->getLayer()->getProductCollection();
        if ($this->enableMultiSelect) {
            $this->setBeforeApplyFacetedData($this->helper->getBeforeApplyFacetedData($productCollection, null, null, $this->_requestVar));
        }
        $this->helper->collectionRatingFilter($productCollection, $attributeValue);        
        $label = $this->_getRatingLabel($attributeValue);
        $this->getLayer()
            ->getState()
            ->addFilter($this->_createItem($label, $attributeValue));
        if (!$this->enableMultiSelect) {
            $this->_items = [];
        }
        return $this;
    }
    
    
    protected function _getRatingLabel($score)
    {
        if ($this->helper->getRatingFilterType() === 'interval') {
            $maxScore = $score;
            $minScore = $score - 1;
            if ($minScore == 0) {
                return __('1 star');
            }
            return __('%1 < star ≤ %2', $minScore, $maxScore);
        } else {
            return ($score == 1) ? __('%1 star and above', $score) : __('%1 stars and above', $score);
        }
    }
    
    

    protected function _getRatingsData($collection)
    {
        $connection = $collection->getConnection();
        $storeId = $collection->getStoreId();
        $options = [];
        $ratingType = $this->helper->getRatingFilterType();
        if ($ratingType == 'interval') {
            for ($i = 5; $i > 0; $i--) {
                $maxPercent = 100 * $i / 5;
                $minPercent = 100 * ($i-1) / 5;
                $cloneCollection = clone $collection;
                $cloneCollection->getSelect()->where("({$minPercent} < {$this->sqlFieldName}) AND ({$this->sqlFieldName} <= {$maxPercent})");
                $this->helper->attachRatingAvgPercentFieldToCollection($cloneCollection);
                $options[] = [
                    'label' => $this->_getRatingLabel($i),
                    'value' => $i,
                    'count' => $connection->fetchOne($cloneCollection->getSelectCountSql())
                ];
            }
        } else {
            for ($i = 4; $i > 0; $i--) {
                $percent = 100 * $i / 5;
                $cloneCollection = clone $collection;
                $cloneCollection->getSelect()->where("{$this->sqlFieldName} >= {$percent}");
                            
                $this->helper->attachRatingAvgPercentFieldToCollection($cloneCollection);
                
                $options[] = [
                    'label' => $this->_getRatingLabel($i),
                    'value' => $i,
                    'count' => $connection->fetchOne($cloneCollection->getSelectCountSql())
                ];
            }
        }
        return $options;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
     protected function _getItemsData()
     {
        $productCollection = $this->getLayer()->getProductCollection();
        if ($this->helper->isMagentoUp24()) {
            $productCollection = $this->getLayer()->getProductCollection();
            if ($items = $this->getBeforeApplyFacetedData()) {
            } else {
                $items = $productCollection->getFacetedData($this->_requestVar);
            }
            $data = [];
            $max = ($this->helper->getRatingFilterType() === 'interval') ? 5 : 4;
            for ($i = $max; $i > 0; $i--) {
                $item = (isset($items[$i])) ? $items[$i] : ['value' => $i, 'count' => 0];
                $item['label'] = $this->_getRatingLabel($item['value']);
                $data[] = $item;
            }
        } else {
            $data = $this->_getRatingsData($productCollection);
        }
        $this->setData('items_data', $data);
        return $data;
    }
}
