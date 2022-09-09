<?php
/**
 * Copyright © Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ProductLabel\Setup;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleInterface;

class UpgradeData implements UpgradeDataInterface
{
    protected $objectManager;
    
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

    /**
     * UpgradeData constructor.
     *
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        //AggregatedFieldDataConverter $aggregatedFieldConverter,
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();
        if (version_compare($version, '2.2.0', '>=') || version_compare($version, '2.2.0-dev', '>=')) {
            $this->convertSerializedDataToJson($setup);
        }
        $this->fixData($setup);
        $setup->endSetup($setup);
    }
    
    protected function getObjectManager()
    {
        if ($this->objectManager === null) {
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }
        return $this->objectManager;
    }
    
    /**
     * Convert metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     */
    public function convertSerializedDataToJson($setup)
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $this->aggregatedFieldConverter = $this->getObjectManager()->get(\Magento\Framework\DB\AggregatedFieldDataConverter::class);
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('codazon_product_label_entity'),
                    'entity_id',
                    'conditions_serialized'
                )
            ],
            $setup->getConnection()
        );
    }
    
    protected function fixData($setup)
    {
        $connection = $setup->getConnection();
        if ($connection->tableColumnExists($setup->getTable('codazon_product_label_entity'), 'is_active')) {
            $objectManager = $this->getObjectManager();
            $select = $connection->select();
            $select->from(['maintable' => $setup->getTable('codazon_product_label_entity')]);
            $existedLabels = $connection->fetchAll($select);
            
            $moduleSetup = $objectManager->create(\Codazon\ProductLabel\Setup\LabelSetupFactory::class)
                ->create(['setup' => $setup]);
            $moduleSetup->installEntities();
            
            $connection->dropColumn(
                $setup->getTable('codazon_product_label_entity'),
                'is_active'
            );
            
            foreach ($existedLabels as $label) {
                $item = $objectManager->create(\Codazon\ProductLabel\Model\ProductLabel::class);
                $item->setStoreId(0);
                $item->load($label['entity_id']);
                $item->setData('is_active', (int)$label['is_active']);
                $item->save();
            }
        }
    }
}
