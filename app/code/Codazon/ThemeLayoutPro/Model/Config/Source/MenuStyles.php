<?php
/**
 * Backend System Configuration reader.
 * Retrieves system configuration form layout from system.xml files. Merges configuration and caches it.
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class MenuStyles implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    
    public function toOptionArray()
    {
        $options = [
            ['value' => 'dropdown', 'label' => __('Dropdown')],
            ['value' => 'popup',    'label' => __('Popup')],
            ['value' => 'sidebar',  'label' => __('Sidebar')]
        ];
        return $options;
    }
}
