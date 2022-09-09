<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\AjaxLayeredNavPro\Plugin\Elasticsearch\Request;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Codazon\AjaxLayeredNavPro\Helper\Data as LayerHelper;

class ConfigReader
{
    protected $helper;
    
    /** Bucket name suffix */
    private const BUCKET_SUFFIX = '_bucket';
    /**
     * @var string
     */
    private $requestNames = [
        'catalog_view_container',
        'quick_search_container'
    ];
    
    /**
     * @var GeneratorResolver
     */
    private $generatorResolver;

    /**
     * @var CollectionFactory
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $exactMatchAttributes = [];
    
    
    public function __construct(
        LayerHelper $helper,
        GeneratorResolver $generatorResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->helper = $helper;
        $this->generatorResolver = $generatorResolver;
        $this->resourceConnection = $resourceConnection;
    }
    
    protected function getFilterableAttributes(): array
    {
        $attributes = [];
        $connection = $this->resourceConnection->getConnection();
        $attributeCodes = [$this->helper->getScopeConfig()->getValue(LayerHelper::RATING_CODE_PATH, 'default')];
        $select = $connection->select()->from($this->resourceConnection->getTableName('core_config_data'), ['value'])
            ->where("path = '" . LayerHelper::RATING_CODE_PATH ."'");
        $attributeCodes = array_unique(array_merge($attributeCodes, $connection->fetchCol($select)));
        foreach ($attributeCodes as $attribute) {
            $attributes[$attribute] = $attribute;
        }
                
        $attributeCodes = [$this->helper->getScopeConfig()->getValue(LayerHelper::STOCK_STATUS_CODE_PATH, 'default')];
        $select = $connection->select()->from($this->resourceConnection->getTableName('core_config_data'), ['value'])
            ->where("path = '" . LayerHelper::STOCK_STATUS_CODE_PATH ."'");
        $attributeCodes = array_unique(array_merge($attributeCodes, $connection->fetchCol($select)));
        foreach ($attributeCodes as $attribute) {
            $attributes[$attribute] = $attribute;
        }
        return $attributes;
    }
    
    public function afterRead(
        \Magento\Framework\Config\ReaderInterface $subject,
        array $result
    ) {
        //'graphql_product_search', 'graphql_product_search_with_aggregation'
        foreach ($this->getFilterableAttributes() as $attributeCode => $attribute) {
            foreach ($this->requestNames as $requestName) {
                if (!empty($result[$requestName])) {
                    $queries = $result[$requestName]['queries'];
                    $filterName = $attributeCode . '_filter';
                    $butketName = $attributeCode . '_bucket';
                    $queryName = $attributeCode . '_query';
                    if (empty($result[$requestName]['queries'][$queryName])) {
                        $result[$requestName]['queries'][$requestName]['queryReference'][] = [
                            'clause' => 'must',
                            'ref' => $queryName
                        ];
                        $result[$requestName]['queries'][$queryName] = [
                            'name'              => $queryName,
                            'type'              => 'filteredQuery',
                            'filterReference'   => [
                                [
                                    'clause'    => 'must',
                                    'ref'       => $filterName
                                ]
                            ]
                        ];
                        $result[$requestName]['filters'][$filterName] = [
                            'name'  => $filterName,
                            'field' => $attributeCode,
                            'value' => '$'.$attributeCode.'$',
                            'type'  => 'termFilter'
                        ];
                        $result[$requestName]['aggregations'][$butketName] = [
                            'name'  => $butketName,
                            'field' => $attributeCode,
                            'type'  => 'termBucket',
                            'metric' => [
                                [
                                    'type' => 'count'
                                ]
                            ]
                        ];
                    }
                }
            }
        }
        return $result;
    }
}
