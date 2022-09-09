<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Codazon\ThemeLayoutPro\Model\Config\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\Scope\Converter;

/**
 * Class for retrieving initial configuration from modules
 */
class RuntimeConfigSource implements ConfigSourceInterface
{
    private $collectionFactory;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;
    
    protected $_coreRegistry;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param Converter $converter
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ScopeCodeResolver $scopeCodeResolver,
        Converter $converter,
        \Magento\Framework\Registry $registry
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->converter = $converter;
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->_coreRegistry = $registry;
    }

    /**
     * Get initial data.
     *
     * @param string $path Format is scope type and scope code separated by slash: e.g. "type/code"
     * @return array
     */
    public function get($path = '')
    {
        $data = new DataObject($this->loadConfig());
        return $data->getData($path) ?: [];
    }

    /**
     * Load config from database.
     *
     * Load collection from db and presents it in array with path keys, like:
     * * scope/key/key *
     *
     * @return array
     */
    private function loadConfig()
    {
        try {
            $collection = $this->collectionFactory->create();
            if ($theme = $this->_coreRegistry->registry('current_theme')) {
                $collection->addFieldToFilter('theme_id', $theme->getId());
            }
        } catch (\DomainException $e) {
            $collection = [];
        }
        $config = [];
        foreach ($collection as $item) {
            if ($item->getScope() === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $config[$item->getScope()][$item->getPath()] = $item->getValue();
            } else {
                $code = $this->scopeCodeResolver->resolve($item->getScope(), $item->getScopeId());
                $config[$item->getScope()][$code][$item->getPath()] = $item->getValue();
            }
        }

        foreach ($config as $scope => &$item) {
            if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $item = $this->converter->convert($item);
            } else {
                foreach ($item as &$scopeItems) {
                    $scopeItems = $this->converter->convert($scopeItems);
                }
            }
        }
        return $config;
    }
}
