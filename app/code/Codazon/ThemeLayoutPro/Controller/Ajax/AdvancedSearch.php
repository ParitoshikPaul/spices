<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\ThemeLayoutPro\Controller\Ajax;

class AdvancedSearch extends \Magento\Framework\App\Action\Action
{    
    protected $helper;
    
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Codazon\Core\Helper\Data $helper
    ) {
        $this->helper = $helper;
		parent::__construct($context);
		
    }
    
    public function execute()
    {
        $selectedValues = $this->getRequest()->getParam('selected_values');
        $newValues = $this->getRequest()->getParam('new_values');
        $result = [];
        if (!empty($newValues)) {
            foreach($newValues as $code => $values) {
                $result[$code] = [];
                foreach ($values as $value) {
                    $fiteredAttrs = $selectedValues;
                    $fiteredAttrs = array_merge($fiteredAttrs, [
                        $code => $value
                    ]);
                    $advancedModel = $this->helper->getObjectManager()->create(\Magento\CatalogSearch\Model\Advanced::class)->addFilters($fiteredAttrs);
                    $this->helper->getCoreRegistry()->unRegister('advanced_search_conditions');
                    $result[$code][$value] = $advancedModel->getProductCollection()->count();
                }
            }
         }
         return $this->getResponse()->representJson(json_encode($result));
    }
}