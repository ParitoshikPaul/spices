<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model;

class Footer extends \Codazon\ThemeLayoutPro\Model\ThemeLayoutAbstract
{
    
    const CACHE_TAG = 'themelayout_footer';
    
    protected $_projectPath = 'codazon/themelayout/footer';
    protected $_mainFileName = 'footer-styles.less.css';
    protected $_cssFileName = 'footer-styles.css';
    protected $elementType = 'footer';
    protected $primary = 'footer_id';
    protected $_flexibleLessDir = 'codazon/themelayout/footer/general/flexible';

    protected function _construct()
    {
        $this->_init('Codazon\ThemeLayoutPro\Model\ResourceModel\Footer');
    }
    
    protected function _getMainHtml()
    {
        return $this->getData('content');
    }
}