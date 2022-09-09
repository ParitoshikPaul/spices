<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model\ResourceModel;

class Header extends \Codazon\ThemeLayoutPro\Model\ResourceModel\AbstractElement
{
    protected $storeTable = 'themelayout_header_store';
    protected $linkField = 'header_id';
    protected $storeFields = ['content'];
    
    protected function _construct()
	{
		$this->_init('themelayout_header','header_id');
	}
}