<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Controller\ResultFactory;

class MassStatusAbstract extends \Magento\Backend\App\Action
{
    protected $primary = 'option_id';
    
    protected $modelClass = '\Codazon\Shopbybrandpro\Model\Brand';
    
    protected $collectionFactory = '\Codazon\Shopbybrandpro\Model\ResourceModel\BrandEntity\CollectionFactory';
    
    protected $fieldName = 'is_active';
    
    protected $fieldValue = 1;
    
    const REDIRECT_URL = '*/*/';
    
    protected $successText = 'Your selected items have been enabled.';
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Shopbybrandpro::save');
    }
    
    public function execute()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $attributeId = $attributeId ? $attributeId : $this->_objectManager->get(\Codazon\Shopbybrandpro\Helper\Data::class)->getAttributeId();
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');        
        try {
            if (isset($excluded)) {
                if (!empty($excluded)) {
					if(!is_array($excluded)){
						$excluded = [$excluded];
					}
                    $this->excludedSetStatus($excluded, $attributeId);
                } else {
                    $this->setStatusAll($attributeId);
                }
                $this->messageManager->addSuccessMessage(__($this->successText));
            } elseif (!empty($selected)) {
				if(!is_array($selected)){
					$selected = [$selected];
				}
                $this->selectedSetStatus($selected, $attributeId);
                $this->messageManager->addSuccessMessage(__($this->successText));
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL, ['_current' => true]);
    }
    
       
    protected function setStatusAll($attributeId)
    {
        $collection = $this->_objectManager->get(\Codazon\Shopbybrandpro\Model\ResourceModel\Option\CollectionFactory::class)->create();
        $collection->addFieldToFilter('main_table.attribute_id', $attributeId);
        $selected = $collection->getAllIds();
        $this->selectedSetStatus($selected, $attributeId);
    }
    
    protected function selectedSetStatus(array $selected, $attributeId)
    {
        $colFactory = $this->_objectManager->get($this->collectionFactory);
        $storeId = $this->getRequest()->getParam('store', 0);
        foreach ($selected as $id) {
            $model = $colFactory->create()->addFieldToFilter($this->primary, $id)->getFirstItem();
            if (!$model->getId()) {
                $model->addData([
                    'attribute_id' => $attributeId,
                    $this->primary => $id
                ]);
            }
            $model->setStoreId($storeId);
            $model->setData($this->fieldName, $this->fieldValue)->save();
        }
        return $this;
    }
    
    protected function excludedSetStatus(array $excluded, $attributeId)
    {
        $collection = $this->_objectManager->get(\Codazon\Shopbybrandpro\Model\ResourceModel\Option\CollectionFactory::class)->create();
        $collection->addFieldToFilter('main_table.attribute_id', $attributeId);
        $collection->addFieldToFilter('main_table.option_id',  ['nin' => $excluded]);
        $selected = $collection->getAllIds();
        $this->selectedSetStatus($selected, $attributeId);
    }
    
}