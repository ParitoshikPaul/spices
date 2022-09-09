<?php
/**
 *
 * Copyright © 2020 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\MainContent;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class CloneElement extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\CloneElement
{    
    protected $primary = 'entity_id';
    
    protected $modelClass = 'Codazon\ThemeLayoutPro\Model\MainContent';
    
    protected $titleField = 'themelayout_title';
    
    protected $backUrl = 'themelayoutpro/mainContent/edit';
}