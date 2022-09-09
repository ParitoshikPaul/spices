<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\MainContent;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\SaveAbstract
{
    protected $elementType = 'maincontent';
    protected $primary = 'entity_id';
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\MainContent';
    protected $eventName = 'themelayoutpro_maincontent_prepare_save';
    protected $_updateMsg = 'You saved this page content.';
    protected $_resetMsg = 'The page content has been reset to default.';
    protected $index;
    protected $storeOptions = [];
    protected $identifier;
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_ThemeLayoutPro::maincontent_save');
    }
    
    protected function _filerContent($content, $identifier)
    {
        $result = $this->_objectManager->get(\Codazon\ThemeLayoutPro\Helper\FixData::class)
            ->filerContent($content, $identifier);
        $this->storeOptions = $result['store_options'];
        return $result['content'];
    }
    
    
    public function execute()
	{
        $request = $this->getRequest();
        $data = $request->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create($this->modelClass);
            $id = $this->getRequest()->getParam($this->primary);
            if ($id) {
				$model->setStoreId((int)$request->getParam('store'))->load($id);
			} else {
                unset($data[$this->primary]);
            }
            if ($store = $request->getParam('store')) {
                $data['store_id'] = $store;
            } else {
                $data['store_id'] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }
            if (isset($data['variables'])) {
                $data['variables'] = json_encode($data['variables']);
            }
            if (isset($data['custom_fields'])) {
                if (!empty($data['required_less_component'])) {
                    $less = json_decode($data['required_less_component'], true);
                    $data['custom_fields']['required_less_component'] = $less;
                }
                $data['custom_fields'] = json_encode($data['custom_fields']);
            }
            $resetData = (bool)$request->getParam('reset_default') && $id;
            if ($resetData) {
                $data = array_replace($data, $model->getDefaultData());
            }
            if (isset($data['themelayout_content'])) {
                $data['themelayout_content'] = $this->_filerContent($data['themelayout_content'], $data['identifier']);
                $data['store_options'] = json_encode($this->storeOptions);
            }
            if (isset($data['use_default']) && is_array($data['use_default'])) {
                foreach ($data['use_default'] as $attributeCode => $useDefault) {
                    if ($useDefault) {
                        $data[$attributeCode] = false;
                        if ($attributeCode === 'themelayout_content') {
                            $data['store_options'] = false;
                        }
                    }
                }
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
            //$data['variables'] = json_decode($data['variables'], true);
            $this->_getSession()->setFormData($data);
			return $resultRedirect->setPath('*/*/edit', [$this->primary => $this->getRequest()->getParam($this->primary)]);
        }
    }
    
}