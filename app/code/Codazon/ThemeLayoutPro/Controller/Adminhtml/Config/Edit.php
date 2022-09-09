<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Config;

class Edit extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\Config\AbstractScopeConfig
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_themeFactory;
    
    protected $_coreRegistry = null;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Config\Model\Config $backendConfig
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Codazon\ThemeLayoutPro\Model\Config\Structure $configStructure,
        \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \Magento\Config\Model\Config $backendConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $configStructure, $sectionChecker, $backendConfig);
        $this->resultPageFactory = $resultPageFactory;
        $this->_themeFactory = $themeFactory;
        $this->_coreRegistry = $registry;
    }
    
    public function execute()
    {
        if (!$this->getRequest()->getParam('section')) {
            $this->getRequest()->setParam('section', 'themelayoutpro');
        }
        $current = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');
        $themeId = $this->getRequest()->getParam('theme_id');
        
        if (!$themeId) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath(
                'themelayoutpro/config/index',
                [
                    '_current' => ['website', 'store'],
                    '_nosid' => true
                ]
            );
        }
        $currentTheme = $this->_themeFactory->create()->load($themeId);
        $this->_coreRegistry->register('current_theme', $currentTheme);
        
        /** @var $section \Magento\Config\Model\Config\Structure\Element\Section */
        $section = $this->_configStructure->getElement($current);
        if ($current && !$section->isVisible($website, $store)) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath('adminhtml/*/', ['website' => $website, 'store' => $store]);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codazon_ThemeLayoutPro::settings');
        $resultPage->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo([$current]);
        $resultPage->addBreadcrumb(__('System'), __('System'), $this->getUrl('*\/system'));
        $resultPage->getConfig()->getTitle()->prepend(__('Configuration: %1', [$currentTheme->getThemeTitle()]));
        return $resultPage;
    }
}