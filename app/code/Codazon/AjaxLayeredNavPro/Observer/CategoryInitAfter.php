<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\AjaxLayeredNavPro\Observer;


use Magento\Framework\Event\ObserverInterface;


class CategoryInitAfter implements ObserverInterface
{
    protected $helper;
    
    protected $filterAbleAttributeList;

    public function __construct(
        \Codazon\AjaxLayeredNavPro\Helper\Data $helper,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterAbleAttributeList
    ) {
        $this->helper = $helper;
        $this->filterAbleAttributeList = $filterAbleAttributeList;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getData('controller_action');
        $request = $controller->getRequest();
        if (!$request->getParam('ajax_nav')) {
            $queryValue = $request->getQueryValue();
            if (count($queryValue)) {
                $filterList = $this->filterAbleAttributeList->getList();
                $filterManager = $this->helper->getFilterManager();
                foreach ($queryValue as $code => $labels) {
                    if (!is_array($labels)) {
                        $labels = explode(',', $labels);
                    }
                    if ($code === 'cat') {
                        $optionValue = [];
                        foreach ($labels as $label) {
                            $label = explode('_', $label);
                            $optionValue[] = $label[0];
                        }
                        $optionValue = implode(',', $optionValue);
                        $request->setParam($code, $optionValue);
                        $request->setQueryValue($code, $optionValue);
                    } else {
                        if (count($labels) > 1) {
                            $optionValue = [];
                            if ($item = $filterList->getItemByColumnValue('attribute_code', $code)) {
                                $this->helper->extractExtraOptions($item);
                                if ($item->getData('not_seo')) {
                                    continue;
                                }
                                $allOptions = $item->getSource()->getAllOptions();
                                foreach ($labels as $label) {
                                    foreach ($allOptions as $key => $option) {
                                        if (($filterManager->translitUrl(htmlspecialchars_decode($option['label'])) === $label) || ($option['label'] === $label)) {
                                            $optionValue[] = $option['value'];
                                        }
                                    }
                                }
                            }
                            if (count($optionValue) === count($labels)) {
                                $optionValue = implode(',', $optionValue);
                                $request->setParam($code, $optionValue);
                                $request->setQueryValue($code, $optionValue);
                            }
                        } else {
                            $label = $labels[0];
                            if ($item = $filterList->getItemByColumnValue('attribute_code', $code)) {
                                $this->helper->extractExtraOptions($item);
                                if ($item->getData('not_seo')) {
                                    continue;
                                }
                                foreach ($item->getSource()->getAllOptions() as $key => $option) {
                                    if (($filterManager->translitUrl(htmlspecialchars_decode($option['label'])) === $label) || ($option['label'] === $label)) {
                                        $request->setParam($code, $option['value']);
                                        $request->setQueryValue($code, $option['value']);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
