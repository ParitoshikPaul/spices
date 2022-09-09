<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {
    
    private $mainContentSetupFactory;
    
    protected $objectManager;

    public function __construct(
        \Codazon\ThemeLayoutPro\Setup\MainContentSetupFactory $mainContentSetupFactory
    ) {
        $this->mainContentSetupFactory = $mainContentSetupFactory;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }
    
    
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        $mainContentEntity = \Codazon\ThemeLayoutPro\Model\MainContent::ENTITY;
        $mainContentSetup = $this->mainContentSetupFactory->create(['setup' => $setup]);
        $mainContentSetup->installEntities();
        
        $mainContentSetup->addAttribute(
            $mainContentEntity, 'themelayout_title', ['type' => 'varchar']
        );

        $mainContentSetup->addAttribute(
            $mainContentEntity, 'themelayout_content', ['type' => 'text']
        );

        /* $mainContentSetup->addAttribute(
            $mainContentEntity, 'themelayout_header', ['type' => 'int']
        );

        $mainContentSetup->addAttribute(
            $mainContentEntity, 'themelayout_footer', ['type' => 'int']
        ); */
        
        
        /* Import template */
        $importModel = $this->objectManager->get('\Codazon\ThemeLayoutPro\Model\Import');
        $importModel->importData();
        
        $setup->endSetup();
    }
    
}