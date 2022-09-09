<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Config;

use Magento\Config\Controller\Adminhtml\System\AbstractConfig;
use \Codazon\ThemeLayoutPro\Model\CodazonTheme as MainTheme;
use Magento\Theme\Model\Data\Design\Config as DesignConfig;

class Importdata extends \Magento\Backend\App\AbstractAction
{
    protected $helper;
    
    protected $importModel;
    
    protected $_themeFactory;
    
    protected $_coreRegistry = null;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Codazon\ThemeLayoutPro\Model\Import $importModel,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->importModel = $importModel;
        $this->_themeFactory = $themeFactory;
        $this->_coreRegistry = $registry;
    }
    
    protected function getHelper()
    {
        if ($this->helper === null) {
            $this->helper = $this->_objectManager->create('Codazon\ThemeLayoutPro\Helper\Data');
        }
        return $this->helper;
    }
    
    public function execute()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        if ($themeId) {
            $currentTheme = $this->_themeFactory->create()->load($themeId);
            $this->_coreRegistry->register('current_theme', $currentTheme);
            $helper = $this->getHelper();
            $mainContent = $helper->getMainContentStyle();
            $header = $helper->getHeaderStyle();
            $footer = $helper->getFooterStyle();
            $newsletterIdentifier = $helper->getConfig('themelayoutpro/general/newsletter_popup');
            $productCustomText = $helper->getConfig('pages/product_view/custom_text');
            $mainMenuIdentifier = $helper->getConfig('themelayoutpro/header/main_menu');
            $verticalMenuIdentifier = $helper->getConfig('themelayoutpro/header/vertical_menu');
            $toggleMenu = $helper->getConfig('themelayoutpro/header/left_menu');
            try {
                $this->importModel->importMainContent($mainContent);
                $this->importModel->importHeader($header);
                $this->importModel->importFooter($footer);
                $this->importModel->importCMSBlock($newsletterIdentifier);
                $this->importModel->importCMSBlock($productCustomText);
                $this->importModel->importMenu($mainMenuIdentifier);
                $this->importModel->importMenu($verticalMenuIdentifier);
                $this->importModel->importMenu($toggleMenu);
                $this->importModel->importTemplateSet();
                $this->importModel->importTemplate();
                $this->messageManager->addSuccess(__('Import data successfully.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages = explode("\n", $e->getMessage());
                foreach ($messages as $message) {
                    $this->messageManager->addError($message);
                }
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
                );
            }
        } else {
            $this->messageManager->addError(__('Theme id is empty.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'themelayoutpro/config/index',
            [
                '_current'  => ['website', 'store', 'section'],
                'theme_id'  => false,
                '_nosid'    => true
            ]
        );
    }
}