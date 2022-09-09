<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model\Eav;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;


class Attribute extends \Magento\Eav\Model\Entity\Attribute implements \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface
{
	const MODULE_NAME = 'Codazon_ThemeLayoutPro';
    
    const ENTITY = 'themelayout_maincontent_eav_attribute';
    
    const KEY_IS_GLOBAL = 'is_global';
	
    protected $_eventObject = 'attribute';
	
    protected $_eventPrefix = 'themelayout_maincontent_entity_attribute';
	
    protected function _construct()
    {
		$this->_init('Codazon\ThemeLayoutPro\Model\ResourceModel\Attribute');
    }
	
    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }
	
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
    }
	
    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }
	
    public function __sleep()
    {
        $this->unsetData('entity_type');
        return parent::__sleep();
    }
}