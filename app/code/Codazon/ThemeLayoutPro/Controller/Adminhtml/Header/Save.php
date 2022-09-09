<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Header;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\SaveAbstract
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
}