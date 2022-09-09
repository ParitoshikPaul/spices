<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\DataProvider\Form\VariablesDataProvider;

use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Store\Model\Store;

class Header extends AbstractModifier
{
    protected $registryName = 'themelayout_header';
    protected $setName = 'header';
    
	public function modifyData(array $data)
    {
        $data = parent::modifyData($data);
		if (is_array($data) && count($data)) {
			foreach ($data as $key => $values) {
                if (isset($data[$key]['content'])) {
					$content = json_decode($data[$key]['content'], true);
                    if (is_array($content)) {
                        $data[$key]['content_1'] = $content['content_1'];
                        $data[$key]['content_2'] = $content['content_2'];
                    } else {
                        $data[$key]['content_1'] = '';
                        $data[$key]['content_2'] = '';
                    }
				}
			}
        }
		return $data;
    }
	
    public function modifyMeta(array $meta)
    {
        $meta = parent::modifyMeta($meta);
        $meta = array_replace_recursive(
            $meta,
            [
                'header_content' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Extra Content'),
                                'collapsible'   => true,
                                'componentType' => Fieldset::NAME,
                                'sortOrder'     => 50
                            ],
                        ],
                    ],
                    'children' => $this->getHeaderContent()
                ],
            ]
        );
        
        return $meta;
    }
    
    protected function getHeaderContent()
    {
        $request = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Request\Http');
        $store = $request->getParam('store', Store::DEFAULT_STORE_ID);
        $children = [];
        $config = [
            'dataScope' => 'content_1',
            'componentType' => Field::NAME,
            'label' => __('Extra Content 1'),
            'source' => 'header',
            'formElement' => 'wysiwyg',
            'wysiwyg' => true,
            'wysiwygConfigData' => [
                'is_pagebuilder_enabled' => false
            ],
            'validation'    => ['required-entry' => false],
            'template'      => 'ui/form/field',
            'notice'        => __('This content is used for some special headers, not applied to all'),
            'scopeLabel'    => __('[STORE VIEW]')
        ];
        
        /* if ($store && ($store != Store::DEFAULT_STORE_ID)) {
            $config['imports'] = [
                'isUseDefault' => '${ $.provider }:data.isUseDefault.content'
            ];
            $config['service'] = [
                'template' => 'Codazon_ThemeLayoutPro/form/helper/scope-service'
            ];
        } */
        
        $children['content_1'] = [
            'arguments' => [
                'data' => [
                    'config' => $config
                ]
            ]
        ];
		
		$config['label'] = __('Extra Content 2');
		$config['dataScope'] = 'content_2';
		$children['content_2'] = [
            'arguments' => [
                'data' => [
                    'config' => $config
                ]
            ]
        ];
        return $children;
    }
}