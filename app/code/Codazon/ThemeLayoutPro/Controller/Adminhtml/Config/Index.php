<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Config;

use \Codazon\ThemeLayoutPro\Model\CodazonTheme as MainTheme;

class Index extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\Config\AbstractScopeConfig
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
	* Core registry
	*
	* @var \Magento\Framework\Registry
	*/
	protected $_coreRegistry = null;
    
    protected $_themeModel;
    
    protected $_themeList = false;
    
    
    
    protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codazon_ThemeLayoutPro::settings');
		return $resultPage;
	}
    
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
        MainTheme $themeModel,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $configStructure, $sectionChecker, $backendConfig);
        $this->resultPageFactory = $resultPageFactory;
        $this->_themeModel = $themeModel;
        $this->_coreRegistry = $registry;
    }
    
    public function execute()
    {
        $themeList = $this->_themeModel->getThemeList();
        $this->_coreRegistry->register(MainTheme::LIST_REGISTER_KEY, $themeList);
        $resultPage = $this->_initAction();
		$resultPage->addBreadcrumb( __('Theme List'),  __('Theme List'));
		$resultPage->getConfig()->getTitle()->prepend(__('Theme List'));
		return $resultPage;
    }
}