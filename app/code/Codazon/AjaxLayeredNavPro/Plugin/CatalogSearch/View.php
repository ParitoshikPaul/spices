<?php
/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Codazon\AjaxLayeredNavPro\Plugin\CatalogSearch;

class View 
{
    protected $helper;
    
    public function __construct(
        \Codazon\AjaxLayeredNavPro\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    public function beforeExecute(
        \Magento\CatalogSearch\Controller\Result\Index $controller
    ) {
        
        $request = $controller->getRequest();
        $queryValue = $request->getQueryValue();
        if (count($queryValue) && !$request->getParam('ajax_nav')) {
            $filterList = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\Layer\Search\FilterableAttributeList::class)
                ->getList();
            $filterManager = $this->helper->getFilterManager();
            foreach ($queryValue as $code => $labels) {
                $labels = explode(',', $labels);
                if ($code === 'cat') {
                    $optionValue = [];
                    foreach ($labels as $label) {
                        $label = explode('_', $label);
                        $optionValue[] = $label[0];
                    }
                    $optionValue = implode(',', $optionValue);
                    //$request->setParam($code, $optionValue);
                    $queryValue[$code] = $optionValue;
                } else {
                    if (count($labels) > 1) {
                        $optionValue = [];
                        foreach ($labels as $label) {
                            if ($item = $filterList->getItemByColumnValue('attribute_code', $code)) {
                                foreach ($item->getSource()->getAllOptions() as $key => $option) {
                                    if (($filterManager->translitUrl(htmlspecialchars_decode($option['label'])) === $label)  || ($option['label'] === $label)) {
                                        $optionValue[] = $option['value'];
                                    }
                                }
                                
                            }
                        }
                        if (count($optionValue) === count($labels)) {
                            $optionValue = implode(',', $optionValue);
                            $queryValue[$code] = $optionValue;
                        }
                    } else {
                        $label = $labels[0];
                        if ($item = $filterList->getItemByColumnValue('attribute_code', $code)) {
                            foreach ($item->getSource()->getAllOptions() as $key => $option) {
                                if (($filterManager->translitUrl(htmlspecialchars_decode($option['label'])) === $label)  || ($option['label'] === $label)) {
                                    //$request->setParam($code, $option['value']);
                                    $queryValue[$code] = $option['value'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $request->setQueryValue($queryValue);
        }
    }
    
    public function afterExecute(\Magento\CatalogSearch\Controller\Result\Index $controller)
    {   
        if ($controller->getRequest()->getParam('ajax_nav')) {
            $request = $controller->getRequest();
            $request->setQueryValue('ajax_nav', null);
            $layout =  $this->helper->getLayout();
            $result = [];
            if ($block = $layout->getBlock('search.result')) {
                $result['category_products'] = $block->toHtml();
            }
            if ($block = $layout->getBlock('catalogsearch.leftnav')) {
                $result['catalog_leftnav'] = $block->toHtml();
            }
            if ($block = $layout->getBlock('page.main.title')) {
                $result['page_main_title'] = $block->toHtml();
            }
            $filterManager = $this->helper->getFilterManager();
            $queryValue = $request->getQueryValue();
            $newQueryValue = $queryValue;
            $params = [
                '_current'      => true,
                '_use_rewrite'  => true,
            ];
            if ($block = $layout->getBlock('catalogsearch.navigation.state')) {
                $filters = $block->getActiveFilters();
                $urlParams = [];
                foreach($filters as $filter) {
                    $filterModel = $filter->getFilter();
                    if ($filterModel->getData('skip_seo')) {
                        continue;
                    }
                    $code = $filterModel->getRequestVar();
                    $params[$code] = false;
                    if (isset($newQueryValue[$code])) {
                        $class = get_class($filterModel);
                        if ($class === 'Codazon\AjaxLayeredNavPro\Model\Layer\Filter\Attribute' || $class === 'Magento\CatalogSearch\Model\Layer\Filter\Attribute') {
                            if ((bool)$filterModel->getAttributeModel()->getData('not_seo')) {
                                continue;
                            }
                            $label = $filter->getLabel();
                            if (is_array($label)) {
                                $newQueryValue[$code] = [];
                                foreach ($label as $lb) {
                                    $newQueryValue[$code][] = $filterManager->translitUrl(htmlspecialchars_decode($lb)) ? : $lb;
                                }
                                $newQueryValue[$code] = trim(implode(',', $newQueryValue[$code]));
                            } else {
                                $newQueryValue[$code] = $filterManager->translitUrl(htmlspecialchars_decode($label)) ? : $label;
                            }
                        } elseif ($class === 'Codazon\AjaxLayeredNavPro\Model\Layer\Filter\Category' || $class === 'Magento\CatalogSearch\Model\Layer\Filter\Category') {
                            $label = $filter->getLabel();
                            if (is_array($label)) {
                                $codes = explode(',', $newQueryValue[$code]);
                                $newQueryValue[$code] = [];
                                foreach ($label as $i => $lb) {
                                    $newQueryValue[$code][] = $codes[$i] . '_' . $filterManager->translitUrl(htmlspecialchars_decode($lb)) ? : $lb;
                                }
                                $newQueryValue[$code] = trim(implode(',', $newQueryValue[$code]));
                            } else {
                                $cat = $filterManager->translitUrl(htmlspecialchars_decode($filter->getLabel())) ? : $filter->getLabel();
                                $newQueryValue[$code] = $newQueryValue[$code].'_'.$cat;
                            }
                        }
                    }
                }
                
                if (isset($newQueryValue['cat'])) {
                    if ($request->getParam('id') == $newQueryValue['cat']) {
                        $newQueryValue['cat'] = null;
                    }
                }
                $newQueryValue['ajax_nav'] = null;
                $params['_query'] = $newQueryValue;
                $result['updated_url'] = $block->getUrl('*/*/*', $params);
                $result['updated_url'] = str_replace('%2C', ',', $result['updated_url']);
            }
            $json = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\Controller\Result\JsonFactory')->create();
            $json->setData($result);
            return $json;
        }
    }
}