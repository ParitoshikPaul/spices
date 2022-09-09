<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\ProductLabel\Controller\Adminhtml;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Controller\ResultFactory;

class AbstractMassStatus extends \Magento\Backend\App\Action
{
	const ID_FIELD = 'entity_id';	
	
    const REDIRECT_URL = '*/*/';
	
    protected $collection = 'Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection';
	
    protected $model = 'Magento\Framework\Model\AbstractModel';
	
    protected $status = true;
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_ProductLabel::save');
    }
    
	public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');		
        $storeId = $this->getRequest()->getParam('store', 0);
        try {
            if (isset($excluded)) {
                if (!empty($excluded)) {
					if(!is_array($excluded)){
						$excluded = [$excluded];
					}
                    $this->excludedSetStatus($excluded, $storeId);
                } else {
                    $this->setStatusAll($storeId);
                }
            } elseif (!empty($selected)) {
				if(!is_array($selected)){
					$selected = [$selected];
				}
                $this->selectedSetStatus($selected, $storeId);
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
	
    protected function setStatusAll($storeId)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $this->setStatus($collection, $storeId);
    }
	
    protected function excludedSetStatus(array $excluded, $storeId)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
        $this->setStatus($collection, $storeId);
    }
    
	protected function selectedSetStatus(array $selected, $storeId)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $this->setStatus($collection, $storeId);
    }
	
    protected function setStatus($collection, $storeId)
    {
        foreach ($collection->getAllIds() as $id) {
            /** @var \Magento\Framework\Model\AbstractModel $model */
            $model = $this->_objectManager->create($this->model);
            $model->setStoreId($storeId)->load($id);
            $model->setData('is_active', $this->status);
            $model->save();
        }
    }
}
