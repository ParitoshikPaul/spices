<?php
/**
 * Copyright © 2020 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Backend;

class ProductViewCustomStyle extends \Codazon\ThemeLayoutPro\Model\Config\ThemeConfigValue
{
	public function afterSave()
    {
		$lessFile = trim($this->getValue());
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$config = $objectManager->get('Codazon\ThemeLayoutPro\App\Config');
		$helper = $objectManager->get('Codazon\ThemeLayoutPro\Helper\Data');
		$mainContent = $helper->getMainContent();
		
		try {
            gc_disable();
			$customField = $mainContent->getData('custom_fields');
			$customField = $customField ? json_decode($customField, true) : [];
            $flexibleFileList = $mainContent->getFlexibleFileList();
			if (!empty($lessFile) && in_array($lessFile, $flexibleFileList)) { /* File exist */
                $customField['product_view_custom_less'] = $lessFile;
                $customField['product_view_less'] = null;
            } elseif (isset($customField['product_view_custom_less'])) {
                unset($customField['product_view_custom_less']);
            }
			
			$mainContent->setData('custom_fields', json_encode($customField));
			$mainContent->save();
			$mainContent->updateWorkspace(false);
            gc_enable();
		} catch(\Exceptions $e) {
			
		}
		
        return parent::afterSave();
    }
}