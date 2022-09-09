<?php
/**
* Copyright © 2020 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\GoogleAmpManager\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {
    
    private $setupFactory;
    
    private $importHelper;
    
    public function __construct(
        \Codazon\GoogleAmpManager\Setup\GoogleAmpManagerSetupFactory $setupFactory,
        \Codazon\GoogleAmpManager\Helper\Import $importHelper
    ) {
        $this->setupFactory = $setupFactory;
        $this->importHelper = $importHelper;
    }
    
    
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /* TO DO */
        $moduleSetup = $this->setupFactory->create(['setup' => $setup]);
        $moduleSetup->installEntities();
        
        /* import data */
        
        $this->importHelper->importData();
        
        $setup->endSetup();
    }
    
}
