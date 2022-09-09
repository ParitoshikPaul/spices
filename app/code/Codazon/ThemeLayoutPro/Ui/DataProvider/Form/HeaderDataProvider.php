<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\DataProvider\Form;

use Codazon\ThemeLayoutPro\Model\ResourceModel\Header\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Store\Model\Store;

class HeaderDataProvider extends AbstractDataProvider
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
        $this->store = $request->getParam('store');
    }
    
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $this->loadedData[$item->getId()] = $item->setStoreId($this->store)->load($item->getId())->getData();
            $this->loadedData[$item->getId()]['isUseDefault']['content'] = ($item->getStoreId() == Store::DEFAULT_STORE_ID);
            $this->loadedData[$item->getId()]['store'] = $this->store;
        }
        
        $data = $this->dataPersistor->get('themelayout_header_form');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->dataPersistor->clear('themelayout_header_form');
        }
        if($this->loadedData) {
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
                                        'source'        => 'footer',
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