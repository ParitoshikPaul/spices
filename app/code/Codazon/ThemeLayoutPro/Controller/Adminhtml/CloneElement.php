<?php
/**
 *
 * Copyright © 2020 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class CloneElement extends \Magento\Backend\App\Action
{
   
    protected $primary = 'entity_id';
    
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\MainContent';
    
    protected $titleField = 'themelayout_title';
    
    protected $backUrl = 'themelayoutpro/mainContent/edit';
    
    protected $helper;
    
    protected $data;
    
    protected $resultJsonFactory;
    
    protected $fileHelper;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\Json $resultJsonFactory,
        \Codazon\Core\Helper\Data $helper,
        \Codazon\Core\Helper\FileManager $fileHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->fileHelper = $fileHelper;
        parent::__construct($context);
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_ThemeLayoutPro::maincontent_save');
    }
    
    protected function checkDataValidation()
    {
        $storeId = 0;
        $success = 1;
        $result = [
            'success'   =>  false,
            'message'   => []
        ];
        $param = $this->getRequest()->getParams();
        if (empty($param['source_id'])) {
            $result['message'][] = __('Cloning source id is missing.');
            $success *= 0;
        } else {
            $model = $this->_objectManager->create($this->modelClass);
            $model->setStoreId($storeId)->load($param['source_id']);
            if (!$model->getId()) {
                $result['message'][] = __('Cloning source does not exist.');
                $success *= 0;
            }
        }
        if (empty($param['new_title'])) {
            $result['message'][] = __('New title is missing.');
            $success *= 0;
        }
        if (empty($param['new_identifier'])) {
            $result['message'][] = __('New identifier is missing.');
            $success *= 0;
        } else {
            $existedModel = $this->_objectManager->create($this->modelClass)->getCollection()
                    ->addFieldToFilter('identifier', $param['new_identifier'])
                    ->getFirstItem();
            if ($existedModel->getId()) {
                $result['message'][] = __('This identifier has existed. Please use another identifier.');
                $success *= 0;
            }
        }
        $result['success'] = (bool)$success;
        $result['message'] = implode('<br />', $result['message']);
        return $result;
    }
    
    protected function cloneData()
    {
        
    }
    
    public function execute()
	{
        $result = [
            'success'   =>  false
        ];
        $storeId = $this->getRequest()->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $sourceId = $this->getRequest()->getParam('source_id');
        $newTitle = $this->getRequest()->getParam('new_title');
        $newIdentifier = $this->getRequest()->getParam('new_identifier');
        $cloneMode = $this->getRequest()->getParam('mode');
        
        $validateResult = $this->checkDataValidation();
        if (!$validateResult['success']) {
            return $this->resultJsonFactory->setJsonData(json_encode($validateResult));
        }
        
        try {
            $sourceModel = $this->_objectManager->create($this->modelClass)->setStore($storeId)->load($sourceId);
            $destModel = $this->_objectManager->create($this->modelClass);
            $cloneData = $sourceModel->getData();
            unset($cloneData[$this->primary]);
            $cloneData['identifier'] = $newIdentifier;
            $cloneData[$this->titleField] = $newTitle;
            $cloneData['is_active'] = 1;
            if ($cloneMode != '1') {
                $cloneData['parent'] = $sourceModel->getData('identifier');
            }
            $destModel->setStoreId($storeId)->setData($cloneData);
            $destModel->save();
            
            $io = $this->fileHelper->getIo();
            $sourceFile = $sourceModel->getProjectDir() . '/' . $sourceModel->getElementsFileName();
            $destFile = $destModel->getProjectDir() . '/' . $sourceModel->getElementsFileName();
            $io->mkdir($destModel->getProjectDir());
            $sourcePreview = $sourceModel->getProjectDir() . '/preview.jpg';
            $destPreview = $destModel->getProjectDir() . '/preview.jpg';
            if ($cloneMode == '1') {
                $io->cp($sourceFile, $destFile);
            }
            if ($io->fileExists($sourcePreview)) {
                $io->cp($sourcePreview, $destPreview);
            }
            
            $destModel->updateWorkspace(true);
            $message = __('The project %1 (id: %2) was cloned successfully', $sourceModel->getData('identifier'), $sourceId);
            $this->messageManager->addSuccess($message);
            $result = [
                'success'   => true,
                'message'   => $message,
                'id'        => $destModel->getId(),
                'elementUrl'=> $this->getUrl($this->backUrl, [$this->primary => $destModel->getId()])
            ];
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $result['message'] = $e->getMessage();
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
            $result['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->messageManager->addException($e, $e->getMessage());
            $result['message'] = $e->getMessage();
        }
        return $this->resultJsonFactory->setJsonData(json_encode($result));
    }
}