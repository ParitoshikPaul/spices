<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model\ResourceModel\TemplateSet;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
	{
		$this->_init('Codazon\ThemeLayoutPro\Model\TemplateSet', 'Codazon\ThemeLayoutPro\Model\ResourceModel\TemplateSet');
	}
}