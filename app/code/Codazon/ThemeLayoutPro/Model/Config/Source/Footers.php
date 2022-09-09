<?php
/**
 *
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class Footers implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    protected $model = 'Codazon\ThemeLayoutPro\Model\FooterFactory';
    
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
