<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class MainContents implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    protected $model = 'Codazon\ThemeLayoutPro\Model\MainContentFactory';
    
    public function toOptionArray()
    {
        $collection = \Magento\Framework\App\ObjectManager::getInstance()->get($this->model)->create()->getCollection()
            ->addAttributeToSelect('themelayout_title');
        $options = [
            ['value' => '', 'label' => __('---')],
        ];
        if ($collection->count()) {
            foreach ($collection->getItems() as $item) {
                $options[] = ['value' => $item->getIdentifier(), 'label' => $item->getThemelayoutTitle()];
            }
        }
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
