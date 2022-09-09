<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Block\Adminhtml\Config;

use \Codazon\ThemeLayoutPro\Model\CodazonTheme as MainTheme;
use \Magento\Store\Model\ScopeInterface;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThemeList extends \Magento\Backend\Block\Widget
{
    
    protected $_themeModel;
    
    protected $_themeList = false;
    
    protected $_coreRegistry = null;
    
    protected $_scopeConfig;
    
    protected $_activeTheme = null;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        MainTheme $themeModel,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_themeModel = $themeModel;
        $this->_coreRegistry = $registry;
    }
    
    public function getActiveThemeId()
    {
        if (!$this->_activeTheme) {
            if ($store = $this->getRequest()->getParam('store')) {
                $this->_activeTheme = $this->_scopeConfig->getValue('design/theme/theme_id', ScopeInterface::SCOPE_STORE, $store);
            } elseif($website = $this->getRequest()->getParam('website')) {
                $this->_activeTheme = $this->_scopeConfig->getValue('design/theme/theme_id', ScopeInterface::SCOPE_WEBSITES, $website);
            } else {
                $this->_activeTheme = $this->_scopeConfig->getValue('design/theme/theme_id');
            }
        }
        return $this->_activeTheme;
    }
    
    public function getThemeList()
    {
        if ($this->_themeList === false) {
            if ($themeList = $this->_coreRegistry->registry(MainTheme::LIST_REGISTER_KEY)) {
                $this->_themeList = $themeList;
            } else {
                $this->_themeList = $this->_themeModel->getThemeList();
            }
        }
        return $this->_themeList;
    }
    
    public function getConfigUrl($theme)
    {
        if ($theme->getId()) {
            $params = ['theme_id' => $theme->getId()];
            if ($store = $this->getRequest()->getParam('store')) {
                $params['store'] = $store;
            }
            return $this->getUrl('themelayoutpro/config/edit', $params);
        }
        return '';
    }
    
    public function getActivateThemeUrl($theme)
    {
        if ($theme->getId()) {
            $params = ['theme_id' => $theme->getId()];
            if ($store = $this->getRequest()->getParam('store')) {
                $params['store'] = $store;
            }
            return $this->getUrl('themelayoutpro/config/activate', $params);
        }
        return '';
    }
    
    public function getImportDataUrl($theme)
    {
        if ($theme->getId()) {
            $params = ['theme_id' => $theme->getId()];
            $params['_current'] = true;
            return $this->getUrl('themelayoutpro/config/importdata', $params);
        }
        return '';
    }
    
    public function getThemeVersion()
    {
        return $this->_themeModel->getThemeVersion();
    }
}