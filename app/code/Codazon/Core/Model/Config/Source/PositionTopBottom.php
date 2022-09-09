<?php
/**
 *
 * Copyright © 2022 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\Core\Model\Config\Source;

class PositionTopBottom implements \Magento\Framework\Option\ArrayInterface
{
    
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Top')],
            ['value' => '1', 'label' => __('Bottom')]
        ];
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
