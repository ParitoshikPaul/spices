<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Setup;

use Magento\Eav\Setup\EavSetup;

class MainContentSetup extends EavSetup {
    
    public function getDefaultEntities() {

        $mainContentEntity = \Codazon\ThemeLayoutPro\Model\MainContent::ENTITY;

        $entities = [
            $mainContentEntity => [
                'entity_model' => 'Codazon\ThemeLayoutPro\Model\ResourceModel\MainContent', //the full resource model class name 
                'attribute_model' => 'Codazon\ThemeLayoutPro\Model\Eav\Attribute',
                'entity_attribute_collection' => 'Codazon\ThemeLayoutPro\Model\ResourceModel\Attribute\Collection', 
                'table' => $mainContentEntity . '_entity',
                'attributes' => [
                    'themelayout_title' => [
                        'type' => 'static',
                    ],
                    'themelayout_content' => [
                        'type' => 'static',
                    ],
                    'themelayout_header' => [
                        'type' => 'static',
                    ],
                    'themelayout_footer' => [
                        'type' => 'static',
                    ],
                ],
            ],
        ];

        return $entities;
    }
}