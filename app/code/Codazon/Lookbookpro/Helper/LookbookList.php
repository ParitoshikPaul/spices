<?php
/**
 * Copyright © 2022 Codazon. All rights reserved.
 * See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

class LookbookList extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_LIST_MODE = 'codazon_lookbook/listing/lookbook_list/list_mode';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    const VIEW_MODE_LIST = 'list';
    const VIEW_MODE_GRID = 'grid';
    const XML_PATH_LIST_DEFAULT_SORT_BY = 'codazon_lookbook/listing/lookbook_list/default_sort_by';

    protected $scopeConfig;

    protected $coreRegistry;

    protected $_defaultAvailableLimit = [10 => 10, 20 => 20, 50 => 50];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $coreRegistry = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry ?? ObjectManager::getInstance()->get(Registry::class);
    }
    
    public function getAvailableViewMode()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_LIST_MODE, ScopeInterface::SCOPE_STORE);

        switch ($value) {
            case 'grid':
                return ['grid' => __('Grid')];

            case 'list':
                return ['list' => __('List')];

            case 'grid-list':
                return ['grid' => __('Grid'), 'list' => __('List')];

            case 'list-grid':
                return ['list' => __('List'), 'grid' => __('Grid')];
        }

        return null;
    }
    
    public function getDefaultViewMode($options = [])
    {
        if (empty($options)) {
            $options = $this->getAvailableViewMode();
        }

        return current(array_keys($options));
    }
    
    public function getDefaultSortField()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LIST_DEFAULT_SORT_BY, ScopeInterface::SCOPE_STORE);
    }
    
    public function getAvailableLimit($viewMode): array
    {
        $availableViewModes = $this->getAvailableViewMode();

        if (!isset($availableViewModes[$viewMode])) {
            return $this->_defaultAvailableLimit;
        }

        $perPageConfigPath = 'codazon_lookbook/listing/lookbook_list/' . $viewMode . '_per_page_values';
        $perPageValues = (string)$this->scopeConfig->getValue($perPageConfigPath, ScopeInterface::SCOPE_STORE);
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if ($this->scopeConfig->isSetFlag('codazon_lookbook/listing/lookbook_list/list_allow_all', ScopeInterface::SCOPE_STORE)) {
            return ($perPageValues + ['all' => __('All')]);
        } else {
            return $perPageValues;
        }
    }
    
    public function getDefaultLimitPerPageValue($viewMode): int
    {
        $xmlConfigPath = sprintf('codazon_lookbook/listing/lookbook_list/%s_per_page', $viewMode);
        $defaultLimit = $this->scopeConfig->getValue($xmlConfigPath, ScopeInterface::SCOPE_STORE);

        $availableLimits = $this->getAvailableLimit($viewMode);
        return (int)($availableLimits[$defaultLimit] ?? current($availableLimits));
    }
}