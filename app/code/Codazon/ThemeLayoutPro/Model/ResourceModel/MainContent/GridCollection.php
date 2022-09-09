<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model\ResourceModel\MainContent;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;


class GridCollection extends AbstractCollection
{
    protected function _construct() {
        $this->_init('Codazon\ThemeLayoutPro\Model\MainContent', 'Codazon\ThemeLayoutPro\Model\ResourceModel\MainContent');
    }
}