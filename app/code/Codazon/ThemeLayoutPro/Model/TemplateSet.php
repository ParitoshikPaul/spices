<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model;

class TemplateSet extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Codazon\ThemeLayoutPro\Model\ResourceModel\TemplateSet');
    }
}