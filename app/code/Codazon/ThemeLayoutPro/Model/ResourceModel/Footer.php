<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Footer extends \Codazon\ThemeLayoutPro\Model\ResourceModel\AbstractElement
{
    protected $storeTable = 'themelayout_footer_store';
    protected $linkField = 'footer_id';
    protected $storeFields = ['content'];
    
    
    protected function _construct()
	{
		$this->_init('themelayout_footer', 'footer_id');
	}
}