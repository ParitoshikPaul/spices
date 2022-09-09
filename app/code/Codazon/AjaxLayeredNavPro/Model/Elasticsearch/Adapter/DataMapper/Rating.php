<?php
declare(strict_types=1);

namespace Codazon\AjaxLayeredNavPro\Model\Elasticsearch\Adapter\DataMapper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use \Codazon\AjaxLayeredNavPro\Helper\Data as LayerHelper;

class Rating
{
    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    
    protected $requestVar;
    
    protected $freshProductCollection;
    
    /**
     * Stock constructor.
     * @param Inventory $inventory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        LayerHelper $helper
    ) {
        $this->helper = $helper;
    }

    protected function getFreshProductCollection()
    {
        if ($this->freshProductCollection === null) {
            $this->freshProductCollection = $this->helper->getObjectManager()->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)->create();
        }
        return clone $this->freshProductCollection;
    }
    
    protected function getRatingValue($entityId, $storeId)
    {
        $collection = $this->getFreshProductCollection()->addFieldToFilter('entity_id', $entityId);
        $collection->setStoreId($storeId);
        $this->helper->attachRatingAvgPercentFieldToCollection($collection);
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns([
            'rt.avg_percent as rating'
        ]);
        return $collection->getConnection()->fetchOne($collection->getSelect());
    }
    
    /**
     * @param $entityId
     * @param $storeId
     * @return bool[]|int[]
     * @throws NoSuchEntityException
     */
    public function map($entityId, $storeId, $requestVar, $filterType)
    {
        $value = (float)$this->getRatingValue($entityId, $storeId);
        if (!$value) {
            return [$requestVar => 0];
        }
        if ($filterType === 'interval') {
            for ($pc = 100; $pc >= 20; $pc-=20) {
                $minPercent = $pc - 20;
                if (($minPercent < $value) && ($value <= $pc)) {
                    $value = $pc * 5 / 100;
                    break;
                }
            }
        } else {
            $values = [];
            for ($pc = 80; $pc >= 20; $pc-=20) {
                if ($value >= $pc) {
                    $values[] = $pc * 5 / 100;
                }
            }
            $value = $values;
        }
        
        return [$requestVar => $value];
    }
}