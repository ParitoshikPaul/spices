<?php
declare(strict_types=1);

namespace Codazon\AjaxLayeredNavPro\Plugin\Elasticsearch\BatchDataMapper;

use Codazon\AjaxLayeredNavPro\Model\Elasticsearch\Adapter\DataMapper\Stock as StockDataMapper;
use Codazon\AjaxLayeredNavPro\Model\Elasticsearch\Adapter\DataMapper\Rating as RatingDataMapper;
use Codazon\AjaxLayeredNavPro\Model\ResourceModel\Inventory;
use Magento\Framework\Exception\NoSuchEntityException;
use Codazon\AjaxLayeredNavPro\Helper\Data as LayerHelper;

class ProductDataMapper
{
    /**
     * @var StockDataMapper
     */
    protected $stockDataMapper;

    /**
     * @var Inventory
     */
    protected $inventory;

    /**
     * ProductDataMapper constructor.
     * @param StockDataMapper $stockDataMapper
     * @param Inventory $inventory
     */
    public function __construct(
        StockDataMapper $stockDataMapper,
        RatingDataMapper $ratingDataMapper,
        Inventory $inventory,
        LayerHelper $helper
    ) {
        $this->stockDataMapper = $stockDataMapper;
        $this->ratingDataMapper = $ratingDataMapper;
        $this->inventory = $inventory;
        $this->helper = $helper;
    }

    protected function getRatingFilterType($storeId)
    {
        $type = $this->helper->getConfig(LayerHelper::RATING_FILTER_TYPE_PATH, $storeId) ? : 'link-up';
        $type = explode('-', $type);
        return empty($type[1]) ? 'up' : $type[1];
    }
    /**
     * @param ProductDataMapper $subject
     * @param $documents
     * @param $documentData
     * @param $storeId
     * @param $context
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterMap(
        \Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper $subject,
        $documents,
        $documentData,
        $storeId,
        $context
    ) {
        if ($this->helper->enableFilterByStockStatus($storeId)) {
            $this->inventory->saveRelation(array_keys($documents));
            foreach ($documents as $productId => $document) {
                //@codingStandardsIgnoreLine
                $document = array_merge($document, $this->stockDataMapper->map($productId, $storeId));
                $documents[$productId] = $document;
            }

            $this->inventory->clearRelation();
        }
        if ($this->helper->enableFilterByRating($storeId)) {
            $requestVar = $this->helper->getConfig(LayerHelper::RATING_CODE_PATH, $storeId) ? : LayerHelper::RATING_CODE;
            $filterType = $this->getRatingFilterType($storeId);
            foreach ($documents as $productId => $document) {
                $document = array_merge($document, $this->ratingDataMapper->map(
                    $productId,
                    $storeId,
                    $requestVar,
                    $filterType
                ));
                $documents[$productId] = $document;
            }
        }
        return $documents;
    }
}