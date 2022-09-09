<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Theme\Model\Theme;
use Magento\Framework\App\Filesystem\DirectoryList;


/**
 * Theme Observer model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LayoutLoadBeforeObserver implements ObserverInterface
{
    /**
     * @var Theme
     */
    private $currentTheme;

    /**
     * @var \Magento\Framework\View\Asset\GroupedCollection
     */
    private $pageAssets;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    

     /**
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\View\Asset\GroupedCollection $assets
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Codazon\ThemeLayoutPro\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->context = $context;
        $this->helper = $helper;
        $this->pageConfig = $context->getPageConfig();
        $this->_coreRegistry = $registry;
        $this->currentTheme = $context->getDesignPackage()->getDesignTheme();
        // $this->_coreRegistry->register('current_theme', $this->currentTheme);
        // $this->assetRepo = $context->getAssetRepository();
        // $this->fileSystem = $context->getFilesystem();
        // $this->urlBuilder = $context->getUrlBuilder();
        $this->headerModel = $this->helper->getHeader();
        $this->footerModel = $this->helper->getFooter();
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        if ($this->helper->canUseConfig()) {
            $layout = $observer->getLayout();
            $this->addThemeScripts();
            $update = $layout->getUpdate();
            
            //if (!$update->isLayoutDefined()) {
                $handles = $update->getHandles();
                $layoutXml = '';
                
                if (!in_array('checkout_index_index', $handles)) {
                    $layoutXml .= $this->headerModel->getData('layout_xml');
                } else {
                    if ($this->helper->getConfig('checkout/general/use_normal_layout')) {
                        $update->addPageHandles(['checkout_onecolumn']);
                        $layoutXml .= $this->headerModel->getData('layout_xml');
                    }
                    if ($this->helper->getConfig('checkout/general/display_social_login_buttons')) {
                        $this->pageConfig->addBodyClass('display-social-buttons');
                    }
                }
                
                if (!$this->helper->getRequest()->getParam('is_amp_page')) {
                    if (in_array('catalog_product_view', $handles) || in_array('checkout_cart_configure', $handles) 
                        || in_array('wishlist_index_configure', $handles)) {
                        if ($customHandle = $this->helper->getMainCustomField('product_view_custom_layout')) {
                            $update->addPageHandles([$customHandle]);
                            if ($product = $this->_coreRegistry->registry('current_product')) {
                                $update->addPageHandles([$customHandle . '_type_' . $product->getTypeId()]);
                            }
                        } else {
                            $productviewLayout = $this->helper->getProductViewStyle();
                            $update->addPageHandles([$productviewLayout]);
                            if ($product = $this->_coreRegistry->registry('current_product')) {
                                $update->addPageHandles([$productviewLayout . '_type_' . $product->getTypeId()]);
                            }
                        }
                        if ($customXML = $this->helper->getConfig('pages/product_view/custom_layout_xml')) {
                            $layoutXml .= $customXML;
                        }
                    }
                }
                if ($this->footerModel->getId()) {
                    $layoutXml .= $this->footerModel->getData('layout_xml');
                }
                $update->addUpdate($layoutXml);
            //}
        }
    }
    
    protected function addThemeScripts()
    {
        //$this->helper->addPageAsset($this->pageConfig, ['attributes' => ['src_type' => 'url']]);
        //$this->pageConfig->addPageAsset('Codazon_ThemeLayoutPro::js/angular-material-bundle.js');
        
        // $this->pageConfig->addPageAsset('Codazon_ThemeLayoutPro::js/angular.js');
        // $this->pageConfig->addPageAsset('Codazon_ThemeLayoutPro::js/angular-animate.js');
        // $this->pageConfig->addPageAsset('Codazon_ThemeLayoutPro::js/angular-aria.js');
        // $this->pageConfig->addPageAsset('Codazon_ThemeLayoutPro::js/angular-messages.js');
        // $this->pageConfig->addPageAsset('Codazon_ThemeLayoutPro::js/angular-material.js');
        
        $this->helper->addScripts($this->pageConfig);
        //$this->pageConfig->setElementAttribute('body', 'ng-app', 'CodazonMaterialApp');
        //$this->pageConfig->setElementAttribute('body', 'ng-cloak', '');
        //$this->pageConfig->setElementAttribute('body', 'md-swipe-content', 'false');
    }
    
    /* public function getProductCustomAttributeXml()
    {
        if ($product = $this->_coreRegistry->registry('current_product')) {
            $customAttributes = $this->getConfig('pages/product_view/custom_attribute');
            if ($customAttributes) {
                $customAttributes = json_decode($customAttributes, true);
                $xml = '<body><referenceBlock name="product.info.details">';
                foreach ($customAttributes as $code) {
                    $attribute = $product->getResource()->getAttribute($code);
                    if ($attribute->getId()) {
                        $label = $attribute->getData('store_label');
                        $xml .= '<block class="Magento\Catalog\Block\Product\View\Description" name="product.codazon-attribute-tab-'.$code.'"
                        as="product.codazon-attribute-tab-'.$code.'"
                        template="Magento_Catalog::product/view/attribute.phtml" group="detailed_info">
                            <arguments>
                                <argument name="at_call" xsi:type="string">get'.$this->toCamelCase($code, true).'</argument>
                                <argument name="at_code" xsi:type="string">'.$code.'</argument>
                                <argument name="css_class" xsi:type="string">custom-tab '.$code.'</argument>
                                <argument name="at_label" xsi:type="string">none</argument>
                                <argument name="title" translate="true" xsi:type="string">'.$label.'</argument>
                            </arguments>
                        </block>';
                    }
                }
                $xml .= '</referenceBlock></body>';
            }
        }
        return $xml;
    } */
}
