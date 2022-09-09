<?php
/**
 *
 * Copyright © 2020 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Header;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class CloneElement extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\CloneElement
{
    protected $primary = 'header_id';
    
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\Header';
    
    protected $titleField = 'title';
    
    protected $backUrl = 'themelayoutpro/header/edit';
}