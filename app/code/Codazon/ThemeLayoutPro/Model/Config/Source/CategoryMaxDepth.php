<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class CategoryMaxDepth implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    protected $model = 'Codazon\ThemeLayoutPro\Model\MainContentFactory';
    
    public function toOptionArray()
    {
        $options = [
            ['value' => '', 'label' => __('All Levels')],
        ];
        for ($i = 1; $i <= 3; $i++) {
            $options[] = ['value' => $i, 'label' => $i];
        }
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
