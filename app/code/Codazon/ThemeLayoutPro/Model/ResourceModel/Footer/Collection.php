<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model\ResourceModel\Footer;

use Magento\Store\Model\Store;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
	{
		$this->_init('Codazon\ThemeLayoutPro\Model\Footer', 'Codazon\ThemeLayoutPro\Model\ResourceModel\Footer');
        $this->_map['fields']['store'] = 'store_table.store_id';
	}
    
    protected function _afterLoad()
    {
        // foreach ($this as $item) {
            // $item->load($item->getId());
        // }
        return parent::_afterLoad();
    }
    
    // public function _beforeLoad()
    // {
        // $storeTable = $this->getTable($this->getResource()->getStoreTable());
        // $this->getSelect()->join(
            // ['st' => $storeTable],
            // 'main_table.footer_id = st.footer_id', 
            // ['st.store_id', 'st.content']
        // );
        
        // return parent::_beforeLoad();
    // }
    
    // public function addFieldToFilter($field, $condition = null)
    // {
        // if ($field === 'store_id') {
            // if(is_int($condition)) {
                // $stores = [(int)$condition];
                // $this->getSelect()->where(
                    // 'store_id IN (?)', $stores
                // );
            // }
            // return $this;
        // } else {
            // return parent::addFieldToFilter($field, $condition);
        // }
    // }
    

    
}