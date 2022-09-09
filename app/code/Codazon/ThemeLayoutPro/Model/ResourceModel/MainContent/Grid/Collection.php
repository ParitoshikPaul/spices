<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Model\ResourceModel\MainContent\Grid;

use Magento\Framework\Api;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected function _construct()
    {
		parent::_construct();
    }
    
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $select1 = $this->getConnection()->select();
        
        $select1->from(
            ['vc' => $this->getTable('themelayout_maincontent_entity_varchar')],
            ['attribute_id' => 'attribute_id', 'themelayout_title' => 'value', 'store_id' => 'store_id', 'main_id' => 'entity_id']
        )->joinLeft(['ea' => $this->getTable('eav_attribute')],
            'vc.attribute_id = ea.attribute_id',
            'attribute_code'
        )->where("ea.attribute_code = 'themelayout_title' and vc.store_id=0");
        
        $this->getSelect()
            ->joinLeft(['vc' => $select1], 'main_table.entity_id = vc.main_id')
            ->group('main_table.entity_id');    
    }
    
}