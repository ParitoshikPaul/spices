<?php
/**
 *
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class CMSBlocks implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    protected $blockOptions;
    
    protected $collectionFactory = 'Magento\Cms\Model\ResourceModel\Block\CollectionFactory';
    
    public function toOptionArray()
    {
        if ($this->blockOptions === null) {
            $collection = \Magento\Framework\App\ObjectManager::getInstance()->get($this->collectionFactory)->create();
            $this->blockOptions = [
                ['value' => '', 'label' => __('---')],
            ];
            if ($collection->count()) {
                foreach ($collection->getItems() as $item) {
                    $this->blockOptions[] = ['value' => $item->getIdentifier(), 'label' => $item->getTitle()];
                }
            }
        }
        return $this->blockOptions;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
