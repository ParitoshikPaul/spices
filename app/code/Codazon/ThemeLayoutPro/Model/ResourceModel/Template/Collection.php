<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model\ResourceModel\Template;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
	{
		$this->_init('Codazon\ThemeLayoutPro\Model\Template', 'Codazon\ThemeLayoutPro\Model\ResourceModel\Template');
	}
}