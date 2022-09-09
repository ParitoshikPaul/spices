<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Footer;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\SaveAbstract
{
    protected $elementType = 'footer';
    protected $primary = 'footer_id';
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\Footer';
    protected $eventName = 'themelayoutpro_footer_prepare_save';
    protected $_updateMsg = 'You saved this footer.';
    protected $_resetMsg = 'The footer has been reset to default.';
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_ThemeLayoutPro::footer_save');
    }
}