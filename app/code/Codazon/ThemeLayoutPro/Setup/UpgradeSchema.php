<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    
    protected $_entityValueTypes = [
        ['type' => 'decimal', 'value_type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, 'value_length' => '12,4', 'comment' => 'Main Content Decimal Attribute Backend Table'],
        ['type' => 'datetime', 'value_type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, 'value_length' => null, 'comment' => 'Main Content Datetime Attribute Backend Table'],
        ['type' => 'text', 'value_type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'value_length' => '4M', 'comment' => 'Main Content Text Attribute Backend Table'],
        ['type' => 'int', 'value_type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'value_length' => null, 'comment' => 'Main Content Int Attribute Backend Table'],
        ['type' => 'varchar', 'value_type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'value_length' => 255, 'comment' => 'Main Content Varchar Attribute Backend Table']
    ];
    
    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        
        $mainContentEntity = \Codazon\ThemeLayoutPro\Model\MainContent::ENTITY;
        $entityTable = $mainContentEntity . '_entity';
        
        $table = $connection->newTable($installer->getTable($entityTable))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'identifier',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Identifier'
            )->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false,  'default' => '1'],
                'Is Active'
            )->addColumn(
                'variables',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                "Variables"
            )->addColumn(
                'custom_variables',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                "Custom Variables"
            )->addColumn(
                'custom_fields',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                "Custom fields"
            )->addColumn(
                'parent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                "Parent"
            )->addIndex(
                $installer->getIdxName(
                    $entityTable,
                    ['entity_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            );
        $connection->createTable($table);
        
        /* Entity Values */
        
        foreach ($this->_entityValueTypes as $type) {
            
            $tableName = $entityTable . '_' . $type['type'];
            $valueType = $type['value_type'];
            $valueLength = $type['value_length'];
            $valueComment = $type['comment'];
            
            $table = $connection->newTable($installer->getTable($tableName))
                ->addColumn(
                    'value_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Value ID'
                )->addColumn(
                    'attribute_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Attribute Id'
                )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Store ID'
                )->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Entity Id'
                )->addColumn(
                    'value',
                    $valueType,
                    $valueLength,
                    [],
                    'Value'
                )->addIndex(
                    $setup->getIdxName($tableName,
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['entity_id', 'attribute_id', 'store_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addIndex(
                    $setup->getIdxName($tableName,
                    ['store_id']),
                    ['store_id']
                )->addIndex(
                    $setup->getIdxName($tableName,
                    ['attribute_id']),
                    ['attribute_id']
                )->addForeignKey(
                    $setup->getFkName(
                        $tableName,
                        'attribute_id',
                        'eav_attribute',
                        'attribute_id'
                    ),
                    'attribute_id',
                    $setup->getTable('eav_attribute'),
                    'attribute_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->addForeignKey(
                    $setup->getFkName(
                        $tableName,
                        'entity_id',
                        $entityTable,
                        'entity_id'
                    ),
                    'entity_id',
                    $setup->getTable($entityTable),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->addForeignKey(
                    $setup->getFkName(
                        $tableName, 'store_id', 'store', 'store_id'
                    ),
                    'store_id',
                    $setup->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment($valueComment);
                
            $connection->createTable($table);
        }
        
        $installer->endSetup();
    }
}