<?php
/**
 * Backend System Configuration reader.
 * Retrieves system configuration form layout from system.xml files. Merges configuration and caches it.
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class Headers implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    protected $model = 'Codazon\ThemeLayoutPro\Model\HeaderFactory';
    
    public function toOptionArray()
    {
        $collection = \Magento\Framework\App\ObjectManager::getInstance()->get($this->model)->create()->getCollection();
        $options = [
            ['value' => '', 'label' => __('---')],
        ];
        if ($collection->count()) {
            foreach ($collection->getItems() as $item) {
                $options[] = ['value' => $item->getIdentifier(), 'label' => $item->getTitle()];
            }
        }
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
