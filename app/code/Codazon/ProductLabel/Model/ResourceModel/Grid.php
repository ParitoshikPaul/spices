<?php
namespace Codazon\ProductLabel\Model\ResourceModel;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult as SearchResult;
use Magento\Framework\Api;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;

class Grid extends SearchResult
{

    protected $_isEav = true;
    
    protected $_entityTypeCode = 'codazon_product_label_entity';
    
    protected $_neededAttributes = ['is_active'];
    
	public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable,
        $resourceModel
    ) {
        
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
			$mainTable,
			$resourceModel
        );
        $this->setMainTable($this->_resource->getTable($mainTable));
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
	
	protected function _beforeLoad()
    {
        parent::_beforeLoad();
        if ($this->_isEav) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $request = $objectManager->get('Magento\Framework\App\Request\Http');
            $entityType = $objectManager->get('Magento\Eav\Model\Config')->getEntityType($this->_entityTypeCode);
            $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $storeId = $request->getParam('store', $defaultStoreId);
            $attributeCollection = $entityType->getAttributeCollection();
            $attributes = [];
            foreach($attributeCollection as $attribute) {
                $code = $attribute->getAttributeCode();
                if (in_array($code, $this->_neededAttributes)) {
                    $attributes[$code] = $attribute->getBackendType();
                }
            }
            
            $i = 0;
            foreach ($attributes as $attributeCode => $backendType) {
                $select[$i] = $this->getConnection()->select();
                $select[$i]->from(
                    ["select{$i}" => $this->getTable($this->_entityTypeCode . '_' . $backendType)],
                    ['attribute_id' => 'attribute_id', $attributeCode => 'value', 'store_id' => 'store_id', 'main_id' => 'entity_id']
                )->joinLeft(['ea' => $this->getTable('eav_attribute')],
                    "select{$i}.attribute_id = ea.attribute_id",
                    ['attr_code' => 'attribute_code']
                )->joinLeft(
                     ['eet' => $this->getTable('eav_entity_type')],
                    'eet.entity_type_id = ea.entity_type_id',
                    ['entity_type_code']
                )->where("ea.attribute_code = '{$attributeCode}' AND eet.entity_type_code = '{$this->_entityTypeCode}'
                    AND select{$i}.store_id IN ({$storeId}, {$defaultStoreId})"
                )->order('store_id desc');
                
                $this->getSelect()->joinLeft(["select{$i}" => $select[$i]], "main_table.entity_id = select{$i}.main_id");
                $i++;
            }
            $this->getSelect()->group('main_table.entity_id');
        }
        return $this;
    }
	
}
?>