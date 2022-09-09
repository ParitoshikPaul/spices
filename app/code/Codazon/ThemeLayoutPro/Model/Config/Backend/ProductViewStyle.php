<?php
/**
 * Copyright © 2018 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Backend;

class ProductViewStyle extends \Codazon\ThemeLayoutPro\Model\Config\ThemeConfigValue
{
	public function afterSave()
    {
		$template = $this->getValue();
		$type = explode('/', $template);
		$type = $type[count($type) - 1];
		$type = str_replace(['catalog_product_view_style'], [''], $type);
		$flexibleLess = '_product-view-style-' . $type . '.less.css';
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$config = $objectManager->get('Codazon\ThemeLayoutPro\App\Config');
		$helper = $objectManager->get('Codazon\ThemeLayoutPro\Helper\Data');
        $store = $helper->getRequest()->getParam('store', 0);
		$mainContent = $helper->getMainContent($store);
		
		try {
            gc_disable();
			$customField = $mainContent->getData('custom_fields');
			$customField = $customField ? json_decode($customField, true) : [];

			
			$flexibleFileList = $mainContent->getFlexibleFileList();
			/* File exist */
            if (in_array($flexibleLess, $flexibleFileList)) {
				$customField['product_view_less'] = $flexibleLess;
			}
			//$customField['flexible_less'] = array_unique($customField['flexible_less']);
			
			
			$mainContent->setData('custom_fields', json_encode($customField));
			$mainContent->save();
			$mainContent->updateWorkspace(false);
            gc_enable();
		} catch(\Exceptions $e) {
			
		}
		
        return parent::afterSave();
    }
}