<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Block\Adminhtml\Footer\Edit\Button;

use Magento\Ui\Component\Control\Container;

/**
 * Class Save
 */
class Save extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'themelayout_footer_form.themelayout_footer_form',
                                'actionName' => 'save',
                                'params' => [
                                    false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
        ];
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options[] = [
            'id_hard' => 'save_and_close',
            'label' => __('Save & Close'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'themelayout_footer_form.themelayout_footer_form',
                                'actionName' => 'save',
                                'params' => [
                                    true
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        if ($this->getFooter()) {
            $options[] = [
                'id_hard' => 'save_and_reset',
                'label' => __('Save & Export to Default'),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'themelayout_footer_form.themelayout_footer_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        [
                                            'export' => 1,
                                            'back' => 'edit'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ];
            $options[] = [
                'id_hard' => 'save_and_export',
                'label' => __('Reset to Default'),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'themelayout_footer_form.themelayout_footer_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        [
                                            'reset_default' => 1,
                                            'back' => 'edit'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ];
        }
        return $options;
    }
}
