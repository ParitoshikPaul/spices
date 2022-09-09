<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class RelationListingStyles implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    
    public function toOptionArray()
    {
        $options = [
            ['value' => 'product/list/list-style01.phtml', 'label' => __('Style 01')],
            ['value' => 'product/list/list-style02.phtml', 'label' => __('Style 02')],
            ['value' => 'product/list/list-style03.phtml', 'label' => __('Style 03')],
            ['value' => 'product/list/list-style04.phtml', 'label' => __('Style 04')]
        ];
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
