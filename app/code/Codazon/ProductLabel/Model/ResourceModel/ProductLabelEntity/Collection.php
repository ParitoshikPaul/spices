<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ProductLabel\Model\ResourceModel\ProductLabelEntity;

class Collection extends  \Codazon\ProductLabel\Model\ResourceModel\Collection\AbstractCollection
{
	protected function _construct()
    {
		$this->_init('Codazon\ProductLabel\Model\ProductLabel', 'Codazon\ProductLabel\Model\ResourceModel\ProductLabelEntity');
    }
}
