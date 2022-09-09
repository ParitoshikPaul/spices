<?php
/**
 * Copyright © 2018 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Backend;

class MainContentStyle extends \Codazon\ThemeLayoutPro\Model\Config\ThemeConfigValue
{
	public function afterSave()
    {
        
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$config = $objectManager->get('Codazon\ThemeLayoutPro\App\Config');
		$helper = $objectManager->get('Codazon\ThemeLayoutPro\Helper\Data');
        
        $productListStyle = $helper->getConfig('pages/category_view/design/template');
        $productViewStyle = $helper->getConfig('pages/product_view/layout');
        $type = explode('/', $productListStyle);
		$type = $type[count($type) - 1];
		$type = str_replace(['list-style', '.phtml'], ['', ''], $type);
		$productListLess = 'product-' . $type . '.less.css';
        
        
		$type = explode('/', $productViewStyle);
		$type = $type[count($type) - 1];
		$type = str_replace(['catalog_product_view_style'], [''], $type);
		$productViewLess = '_product-view-style-' . $type . '.less.css';
		
		
		$mainContent = $objectManager->create('Codazon\ThemeLayoutPro\Model\MainContent')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('identifier', $this->getValue())
            ->getFirstItem();
        
		if ($mainContent->getId()) {
            try {
                gc_disable();
                $customField = $mainContent->getData('custom_fields');
                $customField = $customField ? json_decode($customField, true) : [];

                $flexibleFileList = $mainContent->getFlexibleFileList();
                if (in_array($productListLess, $flexibleFileList)) {
                    $customField['product_list_less'] = $productListLess;
                    
                }
                if (in_array($productViewLess, $flexibleFileList)) {
                    $customField['product_view_less'] = $productViewLess;
                }

                $mainContent->setData('custom_fields', json_encode($customField));
                $mainContent->save();
                $mainContent->updateWorkspace(false);
                gc_enable();
            } catch(\Exceptions $e) {
                
            }
        }
		
        return parent::afterSave();
    }
}