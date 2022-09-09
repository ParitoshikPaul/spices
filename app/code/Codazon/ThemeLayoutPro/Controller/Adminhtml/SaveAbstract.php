<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml;

use Magento\Backend\App\Action;

class SaveAbstract extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\ThemeLayoutAbstract
{

    protected $elementType = 'header';
    protected $primary = 'header_id';
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\Header';
    protected $eventName = 'themelayoutpro_header_prepare_save';
    protected $_updateMsg = 'You saved this header.';
    protected $_resetMsg = 'The header has been reset to default.';
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_ThemeLayoutPro::header_save');
    }
    /**
     * File System
     * @var Magento\Framework\Filesystem
    */
    protected $_filesystem;
    
    /**
     * Directory handler
     * @var Magento\Framework\Filesystem\Directory\Read
    */
    protected $_dirHander;
    
    public function __construct(
        Action\Context $context
    ) {
		parent::__construct($context);
	}
    
	public function execute()
	{
        $request = $this->getRequest();
        $data = $request->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create($this->modelClass);
            $id = $this->getRequest()->getParam($this->primary);
			
			$data = $this->filterData($data);
			
            if ($id) {
				$model->load($id);
			} else {
                unset($data[$this->primary]);
            }
            if ($store = $request->getParam('store')) {
                $data['store_id'] = $store;
            }
                        
            if (isset($data['variables'])) {
                $data['variables'] = json_encode($data['variables']);
            }
            $resetData = (bool)$request->getParam('reset_default') && $id;
            if ($resetData) {
                $data = array_replace($data, $model->getDefaultData());
            }
            $model->addData($data);
                       
            
            $this->_eventManager->dispatch(
				$this->eventName,
				['model' => $model, 'request' => $this->getRequest()]
			);
            try {
				$result = $model->save();
                if ($model->getId()) {
                    $export = (bool)$request->getParam('export');
                    $model->updateWorkspace($export);
                }
                if($resetData) {
                    $message = __($this->_resetMsg);
                } else {
                    $message = __($this->_updateMsg);
                }
                $this->messageManager->addSuccess($message);
                if ($request->getParam('back') == 'edit') {
                    $returnParams = [$this->primary => $model->getId(), '_current' => true, 'back' => false];
                    if ($store) {
                        $returnParams['store'] = $store;
                    }
					return $resultRedirect->setPath('*/*/edit', $returnParams);
				} elseif ($request->getParam('back') == 'new') {
                    return $resultRedirect->setPath('*/*/new', []);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\RuntimeException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addException($e, $e->getMessage());
			}
            $data['variables'] = json_decode($data['variables'], true);
            $this->_getSession()->setFormData($data);
            if ($model->getId()) {
                return $resultRedirect->setPath('*/*/edit', [$this->primary => $model->getId()]);
			} else {
                return $resultRedirect->setPath('*/*/edit', [$this->primary => $this->getRequest()->getParam($this->primary)]);
            }
        }
    }
	
	protected function filterData($data)
	{
		if (isset($data['content_1']) && isset($data['content_2'])) {
			$data['content'] = json_encode(['content_1' => $data['content_1'], 'content_2' => $data['content_2']]);
		}
		return $data;
	}
}
