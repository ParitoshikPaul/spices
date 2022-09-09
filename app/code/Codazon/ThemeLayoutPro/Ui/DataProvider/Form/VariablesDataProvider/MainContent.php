<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\DataProvider\Form\VariablesDataProvider;

use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Store\Model\Store;

class MainContent extends AbstractModifier
{
    protected $registryName = 'themelayout_maincontent';
    
    public function modifyMeta(array $meta)
    {
        $meta = parent::modifyMeta($meta);
        $meta = array_replace_recursive(
            $meta,
            [
                'general' => [
                    'children' => [
                        'themelayout_title' => $this->getThemeLayoutTitle()
                    ]
                ]
            ]
        );
        return $meta;
    }
    
    protected function getThemeLayoutTitle()
    {
        $request = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Request\Http');
        $store = $request->getParam('store', Store::DEFAULT_STORE_ID);
        $children = [];
        $item = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\Registry')->registry('themelayout_maincontent');
        $config = [
            'template' => 'ui/form/field',
            'scopeLabel' => __('[STORE VIEW]')
        ];
        
        if ($store && ($store != Store::DEFAULT_STORE_ID)) {
            $config['imports'] = [
                'isUseDefault' => '${ $.provider }:data.isUseDefault.themelayout_title'
            ];
            $config['disabled'] = ($item->getExistsStoreValueFlag('themelayout_title') != 1);
            $config['service'] = [
                'template' => 'Codazon_ThemeLayoutPro/form/helper/scope-service'
            ];
        }
        
        $children['arguments'] = [
            'data' => [
                'config' => $config
            ]
        ];
        return $children;
    }
    
    // public function modifyData(array $data)
    // {
        // return $data;
    // }
    
}