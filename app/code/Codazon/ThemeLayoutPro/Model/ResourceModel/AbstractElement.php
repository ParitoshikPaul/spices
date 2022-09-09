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

class AbstractElement extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $storeTable = 'themelayout_footer_store';
    protected $linkField = 'footer_id';
    protected $storeFields = ['content'];
    
    protected function _construct()
	{
		
	}
    
    public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
		$connectionName = null
	) {
        $this->_storeManager = $storeManager;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
		parent::__construct($context, $connectionName);
	}
    
    public function getStoreTable()
    {
        return $this->storeTable;
    }
    
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $stores = [(int)$object->getStoreId(), Store::DEFAULT_STORE_ID];
        $linkField = $this->linkField;
        $storeTable = $this->getTable($this->storeTable);
        $select->join(
            ['st' => $storeTable],
            $this->getMainTable() . '.' . $linkField . ' = st.' . $linkField,
            array_merge($this->storeFields, ['store_id'])
        )->where('st.store_id IN (?)', $stores)
            ->order('store_id DESC')
            ->limit(1);
        return $select;
    }
    
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->getId()) {
            if ($this->_storeManager->hasSingleStore()) {
                $store = Store::DEFAULT_STORE_ID;
            } else {
                if ($object->getData('store_id') > -1) {
                    $store = $object->getData('store_id');
                } else {
                    $store = Store::DEFAULT_STORE_ID;
                }
            }
            $exited = $this->lookupStoreId($object->getId(), $store);
            $useDefault = [];
            if ($object->getData('use_default')) {
                $useDefault = $object->getData('use_default');
            }

            foreach($this->storeFields as $storeField) {
                if (isset($useDefault[$storeField]) && $useDefault[$storeField] && ($store != Store::DEFAULT_STORE_ID)) {
                    $this->getConnection()->delete(
                        $this->getTable($this->storeTable),
                        "store_id = {$store} AND {$this->linkField} = {$object->getId()}"
                    );
                } else {
                    if ($exited) {
                        $this->getConnection()->update(
                            $this->getTable($this->storeTable),
                            [
                                $storeField => (string)$object->getData($storeField)
                            ],
                            "store_id = {$store} AND {$this->linkField} = {$object->getId()}"
                        );
                    } else {
                        
                        $this->getConnection()->insert(
                            $this->getTable($this->storeTable),
                            [
                                'store_id' => (int)$store,
                                $this->linkField => (int)$object->getId(),
                                $storeField => (string)$object->getData($storeField)
                            ]
                        );
                    }
                }
            }
            
        }
        return $this;
    }
    
    public function lookupStoreId($id, $store) {
        $connection = $this->getConnection();
        $linkField = $this->linkField;
        $storeTable = $this->getTable($this->storeTable);
                
        $select = $connection->select()
            ->from(['mt' => $this->getMainTable()])
            ->join(
                ['st' => $storeTable],
                'mt.' . $linkField . ' = st.' . $linkField,
                []
            )->where('st.store_id = ?', $store)
            ->where('st.' . $linkField . ' = ' . $id );
        return $connection->fetchOne($select);
    }
}