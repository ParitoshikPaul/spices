<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\AjaxLayeredNavPro\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use \Magento\Framework\App\ObjectManager;
/**
 * Layer price filter based on Search API
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Category extends \Magento\CatalogSearch\Model\Layer\Filter\Category
{   
    private $escaper;

    private $dataProvider;
    
    protected $isMagento24;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        \Codazon\AjaxLayeredNavPro\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $categoryDataProviderFactory,
            $data
        );
        $this->escaper = $escaper;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->helper = $helper;
        $this->isMagento24 = $helper->isMagentoUp24();
    }
    
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->isMagento24) {
            return parent::apply($request);
        }
        if ($this->helper->enableCategoryMultiSelect()) {
            $categoryId = $request->getParam($this->_requestVar) ?: $request->getParam('id');
            if (empty($categoryId)) {
                return $this;
            }
            $categoryIds = explode(',', $categoryId);
            $label = [];
            $dataProvider = clone $this->dataProvider;
            foreach ($categoryIds as $childId) {
                $dataProvider->setCategoryId($childId);
                $child = $dataProvider->getCategory();
                $label[] = $child->getName();
            }

            $category = $this->getLayer()->getCurrentCategory();
            $clonedCollection = clone $this->getLayer()->getProductCollection();
            $this->setFacetedData($clonedCollection->getFacetedData('category'));
            $this->dataProvider->setCategoryId($categoryId);
            $category = $this->dataProvider->getCategory();
            
            //$this->getLayer()->getProductCollection()->addCategoryFilter($category);
            $this->getLayer()->getProductCollection()->addFieldToFilter('category_ids', $categoryIds);       
            
            if ($request->getParam('id') != $category->getId() && $this->dataProvider->isValid()) {
                $this->getLayer()->getState()->addFilter($this->_createItem($label, $categoryId));
            }
            return $this;
        } else {
            return parent::apply($request);
        }
    }
    
    protected function _getItemsData()
    {
        if (!$this->isMagento24) {
            return parent::_getItemsData();
        }
        if ($this->helper->enableCategoryMultiSelect()) {
            /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
            $productCollection = $this->getLayer()->getProductCollection();
            $optionsFacetedData = $this->getFacetedData() ?  $this->getFacetedData() : $productCollection->getFacetedData('category');
            //$category = $this->dataProvider->getCategory();
            $category = $this->getLayer()->getCurrentCategory();
            $categories = $category->getChildrenCategories();
            $collectionSize = $productCollection->getSize();
            $childItems = [];
            if ($category->getIsActive()) {
                foreach ($categories as $category) {
                    if ($category->getIsActive()
                        && isset($optionsFacetedData[$category->getId()])
                        //&& $this->isOptionReducesResults($optionsFacetedData[$category->getId()]['count'], $collectionSize)
                    ) {
                        $this->itemDataBuilder->addItemData(
                            $this->escaper->escapeHtml($category->getName()),
                            $category->getId(),
                            $optionsFacetedData[$category->getId()]['count']
                        );
                        if ($category->getIsAnchor() && ($children = $category->getChildrenCategories())) {
                            foreach ($children as $child) {
                                if ($child->getIsActive() && isset($optionsFacetedData[$child->getId()])) {
                                    $id = $child->getId();
                                    $childItems[] = $id;
                                    $this->itemDataBuilder->addItemData(
                                        $this->escaper->escapeHtml($child->getName()),
                                        $child->getId(),
                                        $optionsFacetedData[$id]['count']
                                    );
                                }
                            }
                        }
                    }
                }
            }
            $this->setChildItems($childItems);
            return $this->itemDataBuilder->build();
        } else {
            return parent::_getItemsData();
        }
    }
}