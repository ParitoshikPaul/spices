<?php
/**
 *
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Header;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassEnable extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\MassStatusAbstract
{
    protected $primary = 'header_id';
    
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\Header';
    
    protected $fieldValue = 1;
}