<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model;

class Header extends \Codazon\ThemeLayoutPro\Model\ThemeLayoutAbstract
{
    
    const CACHE_TAG = 'themelayout_header';
    
    protected $_projectPath = 'codazon/themelayout/header';
    protected $_mainFileName = 'header-styles.less.css';
    protected $_cssFileName = 'header-styles.css';
    protected $elementType = 'header';
    protected $primary = 'header_id';
    protected $_flexibleLessDir = 'codazon/themelayout/header/general/flexible';

    protected function _construct()
    {
        $this->_init('Codazon\ThemeLayoutPro\Model\ResourceModel\Header');
    }
    
    protected function _getElementDirName()
    {
        return $this->getData('identifier');
    }
    
    protected function _getMainHtml()
    {
        return $this->getData('content');
    }
}
