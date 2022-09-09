<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Footer;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Delete extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\ThemeLayoutAbstract
{
    protected $elementType = 'footer';
    protected $primary = 'footer_id';
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\Footer';

    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_ThemeLayoutPro::footer_save');
    }
    
    public function execute()
	{
        if ($id = $this->getRequest()->getParam($this->primary)) {
            $model = $this->_objectManager->create($this->modelClass)->load($id);
            try {
                $model->delete();
                $this->messageManager->addSuccess(__('This item has been deleted.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addException($e, $e->getMessage());
			}
            $this->_redirect("*/*/");
        } else {
            $this->messageManager->addError(__('Item ID is empty.'));
            $this->_redirect("*/*/");
        }
    }
    
}