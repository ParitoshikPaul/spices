<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\DataProvider\Form;

use Codazon\ThemeLayoutPro\Model\ResourceModel\MainContent\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Store\Model\Store;

class MainContentDataProvider extends AbstractDataProvider
{
    private $pool;
    protected $loadedData;
    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\App\Request\Http $request,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->pool = $pool;
        $this->request = $request;
        $this->store = $request->getParam('store', 0);
    }
    
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $item = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\Registry')->registry('themelayout_maincontent');
        $item->setData('store', $this->store);
        $this->loadedData[$item->getId()] = $item->getData();
        
        $attributes = ['themelayout_title', 'themelayout_content'];
        
        foreach ($attributes as $attribute) {
            $this->loadedData[$item->getId()]['isUseDefault'][$attribute] = ($item->getExistsStoreValueFlag($attribute) != 1);
        }
        
        $data = $this->dataPersistor->get('themelayout_maincontent_form');
        
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->dataPersistor->clear('themelayout_maincontent_form');
        }
        if ($this->loadedData) {
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $this->loadedData = $modifier->modifyData($this->loadedData);
            }
        }
        return $this->loadedData;
    }
    
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta = array_replace_recursive(
            $meta,
            [
                'general' => [
                    'children' => [
                        'store' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'dataScope'     => 'store',
                                        'formElement'   => 'hidden',
                                        'source'        => 'maincontent',
                                        'sortOrder'     => 0
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );        
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        return $meta;
    }
    
}