<?php
/**
 *
 * Copyright © 2019 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class SizeChartTypes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    
    public function toOptionArray()
    {
        $options = [
            ['value' => 'cms_page',                 'label' => __('Link to a CMS page')],
            ['value' => 'cms_block',                'label' => __('Popup - content is a CMS block')],
            ['value' => 'product_attribute',        'label' => __('Popup - content is a product attribute value')],
            ['value' => 'product_attribute_set',    'label' => __('Popup - content depends to product attribute set')],
        ];
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
