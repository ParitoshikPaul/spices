<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\DataProvider\Form\VariablesDataProvider;

use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Store\Model\Store;

class Footer extends AbstractModifier
{
    protected $registryName = 'themelayout_footer';
    protected $setName = 'footer';
    
    // public function modifyData(array $data)
    // {
        // $data = parent::modifyData($data);
        // if (is_array($data) && count($data)) {
            // foreach ($data as $key => $values) {
                // $data[$key]['isUseDefault']['content'] = 1;
            // }
        // }
        // return $data;
    // }
    
    public function modifyMeta(array $meta)
    {
        $meta = parent::modifyMeta($meta);
        $meta = array_replace_recursive(
            $meta,
            [
                'footer_content' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Content'),
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                                'sortOrder' => 50
                            ],
                        ],
                    ],
                    'children' => $this->getFooterContent()
                ],
            ]
        );
        
        return $meta;
    }
    
    protected function getFooterContent()
    {
        $request = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Request\Http');
        $store = $request->getParam('store', Store::DEFAULT_STORE_ID);
        $children = [];
        $config = [
            'dataScope' => 'content',
            'componentType' => Field::NAME,
            'label' => __('Footer Content'),
            'source' => 'footer',
            'formElement' => 'wysiwyg',
            'wysiwyg' => true,
            'wysiwygConfigData' => [
                'is_pagebuilder_enabled' => false
            ],
            'validation' => ['required-entry' => false],
            'template' => 'ui/form/field',
            'scopeLabel' => __('[STORE VIEW]')
        ];
        
        if ($store && ($store != Store::DEFAULT_STORE_ID)) {
            $config['imports'] = [
                'isUseDefault' => '${ $.provider }:data.isUseDefault.content'
            ];
            $config['service'] = [
                'template' => 'Codazon_ThemeLayoutPro/form/helper/scope-service'
            ];
        }
        
        $children['content'] = [
            'arguments' => [
                'data' => [
                    'config' => $config
                ]
            ]
        ];
        return $children;
    }
    
}