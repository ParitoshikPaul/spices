<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class SubcategoriesStyles implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    
    public function toOptionArray()
    {
        $options = [
            ['value' => 'circular',                 'label' => __('Circular Thumbnail')],
            ['value' => 'rectangular',              'label' => __('Rectangular Thumbnail')],
            ['value' => 'rounded-corners',          'label' => __('Rounded Corners Thumbnail')]
        ];
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
