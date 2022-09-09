<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Config;

use Magento\Config\Controller\Adminhtml\System\AbstractConfig;
use \Codazon\ThemeLayoutPro\Model\CodazonTheme as MainTheme;
use Magento\Theme\Model\Data\Design\Config as DesignConfig;

class Activate extends AbstractConfig
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
    
     /**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;
    
    /** @var ReinitableConfigInterface */
    protected $reinitableConfig;

    /** @var IndexerRegistry */
    protected $indexerRegistry;
    
    protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codazon_ThemeLayoutPro::settings');
		return $resultPage;
	}
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \Magento\Config\Model\Config\Factory $configFactory,
        MainTheme $themeModel,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\App\ReinitableConfig $reinitableConfig
    ) {
        parent::__construct($context, $configStructure, $sectionChecker);
        $this->_configFactory = $configFactory;
        $this->_themeModel = $themeModel;
        $this->_coreRegistry = $registry;
        $this->reinitableConfig = $reinitableConfig;
        $this->indexerRegistry = $indexerRegistry;
    }
    
    public function execute()
    {
        $request = $this->getRequest();
        $themeId = $request->getParam('theme_id');
        
        try {
            if ($themeId) {
                $store = $request->getParam('store');
                $section = 'design';
                $website = $this->getRequest()->getParam('website');
                $groups = [
                    'theme' => [
                        'fields' => [
                            'theme_id' => [
                                'value' => $themeId
                            ]
                        ]
                    ]
                ];
                $configData = [
                    'section'   => $section,
                    'website'   => $website,
                    'store'     => $store,
                    'groups'    => $groups
                ];
                $configModel = $this->_configFactory->create(['data' => $configData]);
                $configModel->save();
                $this->reinitableConfig->reinit();
                $this->reindexGrid();
                
                $pageModel = $this->_objectManager->get('\Magento\Cms\Model\Page');
                if ($pageModel->load('codazon-home-page', 'identifier')->getId()) {
                    $section = 'web';
                    $groups = [
                        'default' => [
                            'fields' => [
                                'cms_home_page' => [
                                    'value' => 'codazon-home-page'
                                ]
                            ]
                        ]
                    ];
                    $configData = [
                        'section'   => $section,
                        'website'   => $website,
                        'store'     => $store,
                        'groups'    => $groups
                    ];
                    $configModel = $this->_configFactory->create(['data' => $configData]);
                    $configModel->save();
                    $this->reinitableConfig->reinit();
                    $this->reindexGrid();
                }
                
                $this->messageManager->addSuccess(__('You activated theme successfully.'));
            } else {
                $this->messageManager->addError('Theme ID is empty');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while activate theme:') . ' ' . $e->getMessage()
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'themelayoutpro/config/index',
            [
                '_current' => ['website', 'store', 'theme_id'],
                '_nosid' => true
            ]
        );
    }
    
    protected function reindexGrid()
    {
        $this->indexerRegistry->get(DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();
    }
}