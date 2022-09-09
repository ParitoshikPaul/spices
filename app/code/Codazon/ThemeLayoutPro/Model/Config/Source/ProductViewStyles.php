<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class ProductViewStyles implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    
    public function toOptionArray()
    {
        $options = [
            ['value' => 'catalog_product_view_style01', 'label' => __('Style 01')],
            ['value' => 'catalog_product_view_style02', 'label' => __('Style 02')],
            ['value' => 'catalog_product_view_style03', 'label' => __('Style 03')],
            ['value' => 'catalog_product_view_style04', 'label' => __('Style 04')],
            ['value' => 'catalog_product_view_style05', 'label' => __('Style 05')],
        ];
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
