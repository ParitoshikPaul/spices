<?php
/**
 *
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassDeleteAbstract extends \Magento\Backend\App\Action
{
    protected $primary = 'entity_id';
    
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\MainContent';
    
    protected $fieldValue = 0;
    
    protected $successText = 'Your selected items have been deleted.';
    
    
    public function execute()
    {
        try {
            $ids = $this->getRequest()->getPost('selected', []);
            foreach ($ids as $id) {
                $model = $this->_objectManager->create($this->modelClass);
                $model->load($id)->delete();
            }
            $this->messageManager->addSuccessMessage(__($this->successText));
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
