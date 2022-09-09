<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;

/**
 * Theme Observer model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryPrepareSave implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getCategory();
        if ($category) {
            $requestData = $observer->getRequest()->getPostValue();
            $this->imagePreprocessing($category, $requestData);
        }
    }
    
    protected function imagePreprocessing($category, $requestData)
    {
        $eavConfig = ObjectManager::getInstance()->get(\Magento\Eav\Model\Config::class);
        $entityType = $eavConfig->getEntityType(CategoryAttributeInterface::ENTITY_TYPE_CODE);
        foreach ($entityType->getAttributeCollection() as $attributeModel) {
            $attributeCode = $attributeModel->getAttributeCode();
            $backendModel = $attributeModel->getBackend();
            if (isset($requestData[$attributeCode])) {
                continue;
            }
            if (!$backendModel instanceof \Codazon\ThemeLayoutPro\Model\Category\Attribute\Backend\Image) {
                continue;
            }
            $category->setData($attributeCode, '');
        }
        return $this;
    }
}