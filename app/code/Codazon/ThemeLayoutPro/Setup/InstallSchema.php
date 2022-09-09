<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    protected $_elements = [
        ['code' => 'header', 'display_name' => 'Header'],
        ['code' => 'footer', 'display_name' => 'Footer']
    ];
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        foreach ($this->_elements as $element) {
            $code = $element['code'];
            $displayName = $element['display_name'];
            $tableName = $installer->getTable('themelayout_' . $code);
            $table = $connection->newTable($tableName)->addColumn(
                "{$code}_id",
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                "{$displayName} id"
            )->addColumn(
                'identifier',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                "{$displayName} Identifier"
            )->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Title'
            )->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                "Is {$displayName} active"
            )->addColumn(
                'variables',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                "{$displayName} variables"
            )->addColumn(
                'layout_xml',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                [],
                "{$displayName} layout xml"
            )->addColumn(
                'custom_variables',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                "{$displayName} custom variables"
            )->addColumn(
                'parent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                "{$displayName} Parent"
            )->addColumn(
                'custom_fields',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                "{$displayName} custom fields"
            )->addIndex(
                $installer->getIdxName($tableName, ['identifier']),
                ['identifier']
            )->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['title'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['title'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )->setComment(
                "Codazon Theme Layout {$displayName} Table"
            );
            $connection->createTable($table);
            
            $table = $connection->newTable(
                $setup->getTable("themelayout_{$code}_store")
            )->addColumn(
                "{$code}_id",
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                "{$displayName} Id"
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                11,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store Id'
            )->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '4M',
                ['nullable' => true],
                "{$displayName} Content"
            )->addIndex(
                $setup->getIdxName(
                    "themelayout_{$code}_store",
                    ["{$code}_id", 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ["{$code}_id", 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    "themelayout_{$code}_store", "{$code}_id", "themelayout_{$code}", "{$code}_id"
                ),
                "{$code}_id",
                $setup->getTable("themelayout_{$code}"),
                "{$code}_id",
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    "themelayout_{$code}_store", 'store_id', 'store', 'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                "{$displayName} Store Table"
            );
            $connection->createTable($table);
            
        }
        
        
        
        /* Theme Config Data Table */
        $table = $connection->newTable(
            $setup->getTable('themelayout_config_data')
        )->addColumn(
            'config_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Config Id'
        )->addColumn(
            'scope',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            8,
            ['nullable' => false, 'default' => 'default'],
            'Config Scope'
        )->addColumn(
            'scope_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['nullable' => false, 'default' => '0', 'unsigned' => true],
            'Config Scope Id'
        )->addColumn(
            'path',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'general'],
            'Config Path'
        )->addColumn(
            'theme_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Theme ID'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Config Value'
        )->addIndex(
            $setup->getIdxName(
                'themelayout_config_data',
                ['scope', 'scope_id', 'path', 'theme_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['scope', 'scope_id', 'path', 'theme_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $setup->getFkName(
                "themelayout_config_data", 'scope_id', 'store', 'store_id'
            ),
            'scope_id',
            $setup->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Theme Config Data'
        );
        $connection->createTable($table);
        
        
        /* Template Type Table */
        $table = $connection->newTable(
            $setup->getTable('themelayout_template_set')
        )->addColumn(
            'template_set_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['primary' => true, 'identity' => true, 'unsigned' => true, 'nullable' => false],
            'Set id'
        )->addColumn(
            'template_set_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Set name'
        )->addColumn(
            'template_set_image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Preview image'
        )->setComment(
            'Template set'
        );
        $connection->createTable($table);
        
        /* Template Table */
        $table = $connection->newTable(
            $setup->getTable('themelayout_template')
        )->addColumn(
            'template_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['primary' => true, 'identity' => true, 'unsigned' => true, 'nullable' => false],
            'Template id'
        )->addColumn(
            'template_set_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => true, 'unsigned' => true],
            'Template type'
        )->addColumn(
            'template_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Template Name'
        )->addColumn(
            'template_image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Preview image'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Template content'
        )->addForeignKey(
            $setup->getFkName(
                'themelayout_template', 'template_set_id', 'themelayout_template_set', 'template_set_id'
            ),
            'template_set_id',
            $setup->getTable('themelayout_template_set'),
            'template_set_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Template'
        );
        $connection->createTable($table);
        
        
        $installer->endSetup();
    }
}
