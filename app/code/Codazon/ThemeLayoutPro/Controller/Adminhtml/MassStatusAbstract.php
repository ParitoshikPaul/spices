<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Controller\ResultFactory;

class MassStatusAbstract extends \Magento\Backend\App\Action
{
    protected $primary = 'entity_id';
    
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\MainContent';
    
    protected $fieldName = 'is_active';
    
    protected $fieldValue = 1;
    
    const REDIRECT_URL = '*/*/';
    
    protected $successText = 'Your selected items have been enabled.';
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_ThemeLayoutPro::maincontent_save');
    }
    
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');		
        try {
            if (isset($excluded)) {
                if (!empty($excluded)) {
					if(!is_array($excluded)){
						$excluded = [$excluded];
					}
                    $this->excludedSetStatus($excluded);
                } else {
                    $this->setStatusAll();
                }
                $this->messageManager->addSuccessMessage(__($this->successText));
            } elseif (!empty($selected)) {
				if(!is_array($selected)){
					$selected = [$selected];
				}
                $this->selectedSetStatus($selected);
                $this->messageManager->addSuccessMessage(__($this->successText));
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL);
    }
    
    protected function setStatusAll()
    {
        $collection = $this->_objectManager->get($this->modelClass)->getCollection();
        $this->setStatus($collection);
    }
    
    protected function excludedSetStatus(array $excluded)
    {
        $collection = $this->_objectManager->get($this->modelClass)->getCollection();
        $collection->addFieldToFilter($this->primary, ['nin' => $excluded]);
        $this->setStatus($collection);
    }
    
    protected function selectedSetStatus(array $selected)
    {
        $collection = $this->_objectManager->get($this->modelClass)->getCollection();
        $collection->addFieldToFilter($this->primary, ['in' => $selected]);
        $this->setStatus($collection);
    }
    
    protected function setStatus($collection)
    {
        foreach ($collection->getItems() as $model) {
            $model->setData($this->fieldName, $this->fieldValue);
            $model->save();
        }
        return $this;
    }
    
}