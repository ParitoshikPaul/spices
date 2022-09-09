<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\DataProvider\Form\VariablesDataProvider;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Codazon\ThemeLayoutPro\Helper\Media as MediaHelper;
use Codazon\ThemeLayoutPro\Model\Config\Structure\Reader;

abstract class AbstractModifier implements ModifierInterface
{
    protected $registryName = 'themelayout_header';
    protected $setName = 'header';
    protected $urlBuilder;
    protected $settingFields = [];
    
    public function __construct(
        UrlInterface $urlBuilder,
        MediaHelper $mediaHelper,
        Reader $reader,
        \Magento\Framework\Registry $registry
    ) {
        $this->mediaHelper = $mediaHelper;
        $this->urlBuilder = $urlBuilder;
        $this->reader = $reader;
        $this->registry = $registry;
    }
    
    // public function setStore($store) {
        // $this->store = $store;
    // }
    
    public function modifyData(array $data)
    {
        if (is_array($data) && count($data)) {
            foreach ($data as $key => $values) {
                if (isset($data[$key]['variables'])) {
                    $data[$key]['variables'] = (array)json_decode($data[$key]['variables'], true);
                } else {
                    $data[$key]['variables'] = [];
                }
                if (isset($data[$key]['custom_fields'])) {
                    $data[$key]['custom_fields'] = (array)json_decode($data[$key]['custom_fields'], true);
                } else {
                    $data[$key]['custom_fields'] = [];
                }
            }
        }
        return $data;
    }
    
    protected function _getSettingFields()
    {
        if (!$this->settingFields) {
            $settingObject = $this->reader->read();
            $this->settingFields = $settingObject['config']['system']['sections']['variables']['children'];
        }
        return $this->settingFields;
    }
    
    public function modifyMeta(array $meta)
    { 
        $meta = array_replace_recursive(
            $meta,
            [
                'variables' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Font/Color Variables'),
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                                'sortOrder' => 30
                            ],
                        ],
                    ],
                    'children' => $this->getVariablesFields()
                ],
            ]
        );
        return $meta;
    }
    
    
    
    public function getVariablesFields()
    {
        $settings = $this->_getSettingFields();
        $i = 1;
        
        $objecManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $model = $this->registry->registry($this->registryName);
        $hasDefault = false;
        $defaultVariables = [];
        if ($model->getDefaultData()) {
            $hasDefault = true;
            $defaultVariables = $model->getDefaultData();
            $defaultVariables = json_decode($defaultVariables['variables'], true);
        }
        foreach ($settings as $key => $group) {
            $fields[$key] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'collapsible' => true,
                            'componentType' => Fieldset::NAME,
                            'label' => (string)__($group['label']),
                            'sortOrder' => $i * 10,
                            'level' => '1'
                        ]
                    ]
                ]
            ];
            $i++;
            foreach ($group['children'] as $name => $field) {
                unset($field['path']);
                unset($field['_elementType']);
                unset($field['id']);
                unset($field['translate']);
                $field['label'] = (string)__($field['label']);
                if (isset($field['tooltip'])) {
                    $field['tooltip'] = ['description' => $field['tooltip']];
                }
                $config = array_replace([
                    'dataScope'        => $name,
                    'formElement'      => 'input',
                    'source'           => $this->setName,
                    'validation'       => false,
                    'componentType'    => Field::NAME,
                    'tooltip'          => ['description' => '@' . $name],
                    'notice'           => isset($defaultVariables[$name])?__('Default: ') . '['. $defaultVariables[$name] .']':''
                ], $field);
                
                if ($config['formElement'] == 'color') {
                    $config['formElement'] = 'input';
                    $config['elementTmpl'] = 'Codazon_ThemeLayoutPro/form/color';
                    $config['component'] = 'Codazon_ThemeLayoutPro/js/color';
                    $config['prefixElementName'] = 'variables';
                } elseif ($config['formElement'] == 'background') {
                    $config['formElement'] = 'input';
                    $config['elementTmpl'] = 'Codazon_ThemeLayoutPro/form/image';
                    $config['component'] = 'Codazon_ThemeLayoutPro/js/image';
                    $config['uploadUrl'] = $this->urlBuilder->getUrl('themelayoutpro/iframe/show');
                    $config['prefixElementName'] = 'variables';
                    $config['mediaUrl'] = $this->mediaHelper->getSwatchMediaUrl();
                } elseif ($config['formElement'] == 'preview_multiselect') {
                    $config['formElement'] = 'select';
                    $config['component'] = 'Magento_Catalog/js/components/new-category';
                    $config['elementTmpl'] = 'ui/grid/filters/elements/ui-select';
                    $config['multiple'] = true;
                    $config['levelsVisibility'] = '1';
                    $config['listens'] = [
                        'index=create_category:responseData' => 'setParsed',
                        'newOption' => 'toggleOptionSelected'
                    ];
                    $config['chipsEnabled'] = true;
                    $config['filterOptions'] = true;
                    $config['disableLabel'] = true;
                } else {
                    if ($config['formElement'] == 'checkbox') {
                        $config['prefer'] = 'toggle';
                        $config['valueMap'] = [
                            'true'  => '1',
                            'false' => '0'
                        ];
                        if (!isset($config['default'])) {
                            $config['default'] = '1';
                        }
                    }
                    if (!isset($field['dataScope'])) {
                        $config['dataScope'] = 'variables.' . $name;
                    }
                }
                
                $fields[$key]['children'][$name] = [
                    'arguments' => [
                        'data' => [
                            'config' => $config
                        ]
                    ]
                ];
                if (isset($config['sourceModel'])) {
                    $fields[$key]['children'][$name]['arguments']['data']['options'] = $objecManager->get($config['sourceModel']);
                }
            }
        }
        return $fields;
    }
}