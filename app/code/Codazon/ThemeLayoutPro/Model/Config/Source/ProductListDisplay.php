<?php
/**
 *
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class ProductListDisplay implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
        	['value' => 'thumb', 'label' => __('Thumbnail')],
        	['value' => 'name', 'label' => __('Name')],
        	['value' => 'sku', 'label' => __('SKU')],
        	['value' => 'description', 'label' => __('Description')],
        	['value' => 'review', 'label' => __('Review')],
        	['value' => 'price', 'label' => __('Price')],
        	['value' => 'addtocart', 'label' => __('Add to cart')],
        	['value' => 'wishlist', 'label' => __('Wishlist')],
            ['value' => 'compare', 'label' => __('Compare')],
            ['value' => 'quickshop', 'label' => __('Quick Shop')],
            ['value' => 'label', 'label' => __('Label')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
