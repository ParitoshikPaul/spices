<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const HEADER_STYLE = 'themelayoutpro/header/header';
    const HEADER_LEFT_MENU_STYLE = 'themelayoutpro/header/left_menu_style';
    const HEADER_WISHLIST_STYLE = 'themelayoutpro/header/wishlist_style';
    const HEADER_MINI_CART_STYLE = 'themelayoutpro/header/mini_cart_style';
    const HEADER_ACCOUNT_PANEL_STYLE = 'themelayoutpro/header/account_panel_style';
    const ENABLE_DEV_MODE = 'themelayoutpro/env/enable_dev_mode';
    const ENABLE_RTL = 'themelayoutpro/general/enable_rtl';
    
    const FOOTER_STYLE = 'themelayoutpro/footer/footer';
    const MAIN_CONTENT_STYLE = 'themelayoutpro/main_content/main_content';
    const PRODUCT_VIEW_STYLE = 'pages/product_view/layout';
        
    protected $themeNamespace = 'Codazon';
    
    protected $_context;
    
    protected $_coreRegistry;
    
    protected $_scopeConfig;
    
    protected $_storeId;
    
    protected $_headerModel;
    
    protected $_footerModel;
    
    protected $_themeHeader = false;
    
    protected $_themeFooter = false;
    
    protected $_themeMainContent = false;
    
    protected $_lessFilesSet = [];
    
    protected $_mediaUrl = '';
        
    protected $_isDevMode = null;
    
    protected $_themeConfig = null;
    
    protected $_designPackage = null;
    
    protected $_canUseConfig;
	
	protected $_itemsPerRow;
	
	protected $_blockFilter;
    
    protected $_objectManager;
    
    protected $_themeGlobalVariables;
    
    protected $_displayOnList;
    
    protected $_magentoVersion;
    
    protected $_isAdmin;
    
    protected $mainCustomField;
    
    protected $productLazyImg;
    
    protected $currentCategory;
    
    protected $swatchImageSize;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Codazon\ThemeLayoutPro\Model\Header $headerModel,
        \Codazon\ThemeLayoutPro\Model\Footer $footerModel,
        \Codazon\ThemeLayoutPro\Model\MainContent $mainContentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Codazon\ThemeLayoutPro\App\Config $themeConfig,
        \Magento\Framework\Registry $registry
    ) {
        
        $this->_context = $context;
        $this->_coreRegistry = $registry;
        $this->_headerModel = $headerModel;
        $this->_footerModel = $footerModel;
        $this->_mainContentModel = $mainContentModel;
        $this->_storeManager = $storeManager;
        $this->_themeConfig = $themeConfig;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeId = $this->_storeManager->getStore()->getId();
        
        if (!$this->_coreRegistry->registry('current_theme')) {
			$themeProvider = $this->_objectManager->get('Magento\Framework\View\Design\Theme\ThemeProviderInterface');
            try {
                if ($this->isAdmin()) {
                    $themeId = $context->getRequest()->getParam('theme_id');
                    if ($themeId) {
                        $this->_coreRegistry->register('current_theme', $themeProvider->getThemeById($themeId));
                    }
                } else {
                    $themeId = $this->_scopeConfig->getValue(\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $this->_storeId
                    );
                    $this->_coreRegistry->register('current_theme', $themeProvider->getThemeById($themeId));
                }
            } catch (\Exceptions $e) {
                
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                
            }
        }
        
        if (!$this->_coreRegistry->registry('theme_global_variables')) {
            $this->_themeGlobalVariables = new \Magento\Framework\DataObject();
            $variables = [];
            $this->_themeGlobalVariables->setData('variables', $variables);
            $this->_coreRegistry->register('theme_global_variables', $this->_themeGlobalVariables);
        }
        parent::__construct($context);
        $this->_mediaUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
    }
    
    public function isAdmin()
    {
        if ($this->_isAdmin === null) {
            $this->_isAdmin = ($this->_objectManager->get(\Magento\Framework\App\State::class)->getAreaCode() == 'adminhtml');
        }
        return $this->_isAdmin;
    }
    
    public function getRequest()
    {
        return $this->_context->getRequest();
    }
    
    public function getUrl($path, $params = [])
    {
        return $this->_urlBuilder->getUrl($path, $params);
    }
    
    public function getThemeGlobalVariable($name)
    {
        $themeGlobalVariables = $this->_coreRegistry->registry('theme_global_variables');
        $variables = $themeGlobalVariables->getData('variables');
        if (isset($variables[$name])) {
            return $variables[$name];
        }
        return false;
    }
    
    public function getCoreRegistry()
    {
        return $this->_coreRegistry;
    }
    
    public function setThemeGlobalVariable($name, $value)
    {
        $themeGlobalVariables = $this->_coreRegistry->registry('theme_global_variables');
        $variables = $themeGlobalVariables->getData('variables');
        $variables[$name] = $value;
        $themeGlobalVariables->setData('variables', $variables);
        return $this;
    }
    
    public function addThemeGlobalVariable(array $values)
    {
        $themeGlobalVariables = $this->_coreRegistry->registry('theme_global_variables');
        $variables = $themeGlobalVariables->getData('variables');
        $variables = array_replace($variables, $values);
        $themeGlobalVariables->setData('variables', $variables);
        return $this;
    } 
    
    public function getThemeGlobalVariables()
    {
        $themeGlobalVariables = $this->_coreRegistry->registry('theme_global_variables');
        return $themeGlobalVariables->getData('variables');
    }
    
    public function getHeader()
    {
        if(!$this->_themeHeader) {
            if (!$this->_coreRegistry->registry('themelayout_header')) {
                $header = $this->_headerModel->setStoreId($this->_storeId)->load($this->getHeaderStyle(), 'identifier');
                $this->_coreRegistry->register('themelayout_header', $header);
            }
            $this->_themeHeader = $this->_coreRegistry->registry('themelayout_header');
        }
        return $this->_themeHeader;
    }
    
    public function getCurrentTheme()
    {
        return $this->_coreRegistry->registry('current_theme');
    }
    
    public function canUseConfig()
    {
        if ($this->_canUseConfig === null) {
            if (strpos((string)$this->_coreRegistry->registry('current_theme')->getData('code'), $this->themeNamespace) === 0) {
                $this->_canUseConfig = true;
            } elseif (strpos((string)$this->_objectManager->get(\Magento\Framework\View\Design\Theme\ThemeProviderInterface::class)
                ->getThemeById($this->_coreRegistry->registry('current_theme')->getData('parent_id'))->getData('code'), $this->themeNamespace) === 0) {
                $this->_canUseConfig = true;
            } else {
                $this->_canUseConfig = false;
            }
        }
        return $this->_canUseConfig;
    }
    
    public function getFooter()
    {
        if(!$this->_themeFooter) {
            if (!$this->_coreRegistry->registry('themelayout_footer')) {
                $footer = $this->_footerModel
                    ->setStoreId($this->_storeId)
                    ->load($this->getFooterStyle(), 'identifier');
                $this->_coreRegistry->register('themelayout_footer', $footer);
            }
            $this->_themeFooter = $this->_coreRegistry->registry('themelayout_footer');
        }
        return $this->_themeFooter;
    }
    public function getMainContent($storeId = false)
    {
        if (!$this->_themeMainContent) {
            if (!$this->_coreRegistry->registry('themelayout_maincontent')) {
                $storeId = ($storeId === false) ? $this->_storeManager->getStore()->getId() : $storeId;
                $mainContent = $this->_mainContentModel
                    ->getCollection()
                    ->setStoreId($storeId)
                    ->addFieldToFilter('identifier', $this->getMainContentStyle())
                    ->addFieldToFilter('is_active', 1)
                    ->addAttributeToSelect('themelayout_content')
                    ->getFirstItem();
                $this->_coreRegistry->register('themelayout_maincontent', $mainContent);
            }
            $this->_themeMainContent = $this->_coreRegistry->registry('themelayout_maincontent');
        }
        return $this->_themeMainContent;
    }
    
    public function getConfig($path)
    {
        return $this->_themeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeId);
    }
    
    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }
    
    public function getProductViewStyle()
    {
        return $this->getMainCustomField('product_view_style'); //$this->getConfig(self::PRODUCT_VIEW_STYLE);
    }
    
    public function getHeaderStyle()
    {
        return $this->getConfig(self::HEADER_STYLE);
    }
    public function getFooterStyle()
    {
        return $this->getConfig(self::FOOTER_STYLE);
    }
    public function getMainContentStyle()
    {
        return $this->getConfig(self::MAIN_CONTENT_STYLE);
    }
    
    // public function getLeftMenuStyle() {
        // return $this->getConfig(self::HEADER_LEFT_MENU_STYLE);
    // }
    
    public function getHeaderWishlistStyle() {
        return $this->getConfig(self::HEADER_WISHLIST_STYLE);
    }
    
    public function getMiniCartStyle() {
        return $this->getConfig(self::HEADER_MINI_CART_STYLE);
    }
    
    public function getAccountPanelStyle() {
        return $this->getConfig(self::HEADER_ACCOUNT_PANEL_STYLE);
    }
    
    public function isDeveloperMode() {
        if($this->_isDevMode === null) {
            $this->_isDevMode = (bool)$this->getScopeConfig(self::ENABLE_DEV_MODE);
        }
        return $this->_isDevMode;
    }
    
    public function getMediaUrl($path = '') {
        return $this->_mediaUrl . $path;
    }
    
    public function getHeaderCssFile() {
        return str_replace(['https://', 'http://'], ['//', '//'], $this->_mediaUrl . $this->getHeader()->getMainCssFileRelativePath($this->getConfig(self::ENABLE_RTL))) . '?version='. $this->getHeader()->getVersion();
    }
    
    public function getFooterCssFile() {
        return str_replace(['https://', 'http://'], ['//', '//'], $this->_mediaUrl . $this->getFooter()->getMainCssFileRelativePath($this->getConfig(self::ENABLE_RTL))) . '?version='. $this->getFooter()->getVersion();
    }
    
    public function getMainContentCssFile() {
        return str_replace(['https://', 'http://'], ['//', '//'], $this->_mediaUrl . $this->getMainContent()->getMainCssFileRelativePath($this->getConfig(self::ENABLE_RTL))) . '?version='. $this->getMainContent()->getVersion();
    }
    
    public function getSecondaryCssFile($styleName) {
        return str_replace(['https://', 'http://'], ['//', '//'], $this->_mediaUrl . $this->getMainContent()->getSecondaryCssFile($styleName, $this->getConfig(self::ENABLE_RTL))) . '?version='. $this->getMainContent()->getVersion();
    }
    
    public function getLessFilesSet() {
        if (!$this->_lessFilesSet) {
            $mediaUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);            
            if ($this->isDeveloperMode()) {
                $header = $this->getHeader();
                $lessFile = $mediaUrl . $header->getMainLessFileRelativePath(); 
                $this->_lessFilesSet[] = $lessFile;
                
                $footer = $this->getFooter();
                $lessFile = $mediaUrl . $footer->getMainLessFileRelativePath();
                $this->_lessFilesSet[] = $lessFile;
                
                $mainContent = $this->getMainContent();
                $lessFile = $mediaUrl . $mainContent->getMainLessFileRelativePath();
                $this->_lessFilesSet[] = $lessFile;
            }
        }
        
        return $this->_lessFilesSet;
    }
    
    public function getCssScript($layout)
    {
        $script = '';
        if ($this->canUseConfig()) {            
            if (!$this->isDeveloperMode()) {
                if ($this->getHeader()->cssFileExisted()) {
                    $script .= '<link id="cdz-header-css" rel="stylesheet" type="text/css"  media="all" href="' .$this->getHeaderCssFile(). '" />'."\n";
                }
                if ($this->getFooter()->cssFileExisted()) {
                    $script .= '<link id="cdz-footer-css" rel="stylesheet" type="text/css"  media="all" href="' .$this->getFooterCssFile(). '" />'."\n";
                }
                if ($this->getMainContent()->cssFileExisted()) {
                    $handles = $layout->getUpdate()->getHandles();
                    $handlesStr = implode('-', $handles);
                    if (stripos($handlesStr, 'blog')) {
                        $script .= '<link id="cdz-blog-css" rel="stylesheet" type="text/css"  media="all" href="' .$this->getSecondaryCssFile('blog-styles'). '" />'."\n";
                    } elseif (in_array('catalog_product_view', $handles) || in_array('checkout_cart_configure', $handles) 
                                || in_array('wishlist_index_configure', $handles)) {
                        $script .= '<link id="cdz-product-view-css" rel="stylesheet" type="text/css"  media="all" href="' .$this->getSecondaryCssFile('product-view-styles'). '" />'."\n";
                    } else {
                        $script .= '<link id="cdz-maincontent-css" rel="stylesheet" type="text/css"  media="all" href="' .$this->getMainContentCssFile(). '" />'."\n";
                    }
                }
            }
            if ($customCssFile = $this->getConfig('themelayoutpro/general/custom_css_file')) {
                $script .= '<link id="cdz-custom-css" rel="stylesheet" type="text/css"  media="all" href="' .$this->getMediaUrl($customCssFile). '" />'."\n";
            }
        }
        return $script;
    }
    
    public function getMainCustomField($id = null)
    {
        if ($this->mainCustomField === null) {
            $this->mainCustomField = json_decode((string)$this->getMainContent()->getCustomFields(), true);
        }
        return $id ? (isset($this->mainCustomField[$id]) ? $this->mainCustomField[$id] : null)  : $this->mainCustomField;
    }
    
    public function addFontScripts($pageConfig)
    {
        $customeFields = $this->getMainCustomField();
        if (isset($customeFields['google_web_fonts']) && $customeFields['google_web_fonts']) {
            $fontUrl = '//fonts.googleapis.com/css?family=';
            $separate = '';
            foreach($customeFields['google_web_fonts'] as $font) {
                $font = str_replace(' ', '+', trim($font));
                $fontUrl .= $separate . $font;
                $fontUrl .= ':' . $customeFields['google_font_weights'];
                $separate = '|';
            }
            $fontUrl .= '&subset=' . $customeFields['google_font_subset'] . '&display=swap';
            $pageConfig->addRemotePageAsset($fontUrl, 'css');
        }
    }
    
    public function addScripts($pageConfig)
    {
        $this->addFontScripts($pageConfig);
        $globalValues = [];
        $globalValues['now'] = date("Y-m-d H:i:s");
        $globalValues['dateTimeUrl'] = $this->_urlBuilder->getUrl('themelayoutpro/ajax/datetime');
        $globalValues['checkoutUrl'] = $this->_urlBuilder->getUrl('checkout');
        $globalValues['enableStikyMenu'] = (bool)$this->getConfig('themelayoutpro/header/enable_sticky_menu');
        $globalValues['alignVerMenuHeight'] = (bool)$this->getConfig('themelayoutpro/header/vertical_menu_align_height');
        $globalValues['customerDataUrl'] = $this->_urlBuilder->getUrl('customer/section/load', ['_query' => ['sections' => 'customer', 'update_section_id' => false]]);
        
        if ($globalValues['rtl'] = (bool)$this->getConfig(self::ENABLE_RTL)) {
            $pageConfig->addBodyClass('rtl-layout');
            $pageConfig->setElementAttribute('body', 'dir', 'rtl');
        }
        if ($this->isDeveloperMode()) {
            $pageConfig->addPageAsset('Codazon_ThemeLayoutPro::js/less.js');
        }
        if ($bodyClass = $this->getConfig('themelayoutpro/general/custom_body_class')) {
            $pageConfig->addBodyClass($bodyClass);
        }
        $this->addThemeGlobalVariable($globalValues);
        return $this;
    }

	public function getCategoryItemsPerRowArray($layout = '1')
	{
		if (empty($this->_itemsPerRow)) {
			$this->_itemsPerRow = [];
			$breakPoints = [1900, 1600, 1420, 1280, 980, 768, 480, 320, 0];
			foreach ($breakPoints as $point) {
				$this->_itemsPerRow[$point] = (float)($this->getConfig("pages/category_view/items_per_row_{$layout}/items_" . $point) ? : 4);
			}
		}
		return $this->_itemsPerRow;
	}
	
    public function getSliderItemsPerRowArray($layout = '1')
	{
		if (empty($this->_sliderItemsPerRow)) {
			$this->_sliderItemsPerRow = [];
			$breakPoints = [1900, 1600, 1420, 1280, 980, 768, 480, 320, 0];
			foreach ($breakPoints as $point) {
				$this->_sliderItemsPerRow[$point] = ['items' => (float)($this->getConfig("pages/category_view/items_per_row_{$layout}/items_" . $point) ? : 4)];
			}
		}
		return $this->_sliderItemsPerRow;
	}
    
	public function getColumnStyle($gridWrap, $gridItem, $generalItem, $layout = '1')
	{
		$itemsPerRows = $this->getCategoryItemsPerRowArray($layout);
		$style = '';
		$prevPoint = 1900;
		$mobileRightMargin = (float)($this->getConfig("pages/category_view/design/margin_right_mobile") ? : 0);
		$mobileBottomMargin = (float)($this->getConfig("pages/category_view/design/margin_bottom_mobile") ? : 0);
		$desktopRightpMargin = (float)($this->getConfig("pages/category_view/design/margin_right_desktop") ? : 0);
		$desktopBottomMargin = (float)($this->getConfig("pages/category_view/design/margin_bottom_desktop") ? : 0);
		$style .= "{$gridWrap}{margin-left:0}";
		$style .= "{$gridItem}{margin-left:0}";
		foreach ($itemsPerRows as $point => $columns) {
			$marginRight = ($point < 768) ? $mobileRightMargin : $desktopRightpMargin;
			$marginBottom = ($point < 768) ? $mobileBottomMargin : $desktopBottomMargin;
			
			if ($point > 0 && $point < 1900) {
				$style .= "@media (min-width: {$point}px) and (max-width: {$prevPoint}px){";
			} else {
				if ($point == 0) {
					$style .= "@media (max-width:{$prevPoint}px){";
				} else {
					$style .= "@media (min-width:{$point}px){";
				}
			}
			$prevPoint = $point - 1;
			
			$width = 100/$columns;
			$style .= "{$gridItem}{width:calc({$width}% - {$marginRight}px)}";
			$style .= "}";
		}
		
		$style .= "@media(min-width: 768px){";
		$style .= "{$gridWrap}{margin-right:-{$desktopRightpMargin}px}";
		$style .= "{$generalItem}{margin-bottom:{$desktopBottomMargin}px}";
		$style .= "{$gridItem}{margin-right:{$desktopRightpMargin}px}";
		$style .= "}";
		$style .= "@media(max-width: 767px){";
		$style .= "{$gridWrap}{margin-right:-{$mobileRightMargin}px}";
		$style .= "{$generalItem}{margin-bottom:{$mobileBottomMargin}px}";
		$style .= "{$gridItem}{margin-right:{$mobileRightMargin}px}";
		$style .= "}";
		
		return $style;
	}
	
	public function getDisplayOnListArray()
	{
		if ($this->_displayOnList === null) {
			$this->_displayOnList = explode(',', $this->getConfig('pages/category_view/design/show'));
		}
		return $this->_displayOnList;
	}
	
	public function isDisplayOnList($attribute)
	{
		return in_array($attribute, $this->getDisplayOnListArray());
	}
	
	/* public function subString($str, $strLenght)
	{
		$str = strip_tags($str);
        if(strlen($str) > $strLenght) {
            $strCutTitle = substr($str, 0, $strLenght);
            $str = substr($strCutTitle, 0, strrpos($strCutTitle, ' '))."&hellip;";
        }
        return $str;
	} */
    public function subString($str, $strLenght)
	{
		$str = strip_tags($str);
        if(mb_strlen($str) > $strLenght) {
            $strCutTitle = mb_substr($str, 0, $strLenght);
            $str = mb_substr($strCutTitle, 0, mb_strrpos($strCutTitle, ' '))."&hellip;";
        }
        return $str;
	}
	
	public function getBlockPageLayout($block)
	{
		$pageLayout = \Magento\Framework\App\ObjectManager::getInstance()
			->get('Magento\Framework\View\Page\Config')->getPageLayout() ? :
			$block->getLayout()->getUpdate()->getPageLayout();
		switch($pageLayout) {
			case '1column':
				$layout = 1; break;
			case '3columns':
				$layout = 3; break;
			case '2columns-left':
			case '2columns-right':
			default:
				$layout = 2; break;
		}
		return $layout;
	}
    
    public function getGeocodeByAddress($address)
    {
		return json_decode(file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false'));
	}
    
	public function getGoogleMapJavascriptUrl()
    {
        $key = $this->getConfig('pages/contact/google_api_key');
        if ($key) {
            return "//maps.googleapis.com/maps/api/js?v=weekly&key={$key}";
        } else {
            return 'https://maps.googleapis.com/maps/api/js?key';
        }
	}
    
    public function getMapAdditionalMarkers()
    {
        $markers = $this->getConfig('pages/contact/map_additional_markers');
        if ($markers) {
            $result = [];
            $markers = unserialize($markers);
            foreach ($markers as $marker) {
                $result[] = $marker;
            }
            return $result;
        }
        return [];
    }
    
    public function getBlockFilter()
    {
        if ($this->_blockFilter === null) {
            $this->_blockFilter = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Cms\Model\Template\FilterProvider')
            ->getBlockFilter();
        }
        return $this->_blockFilter;
    }
    
    public function htmlFilter($content)
    {
        return $this->getBlockFilter()->filter((string)$content);
    }
    
    public function getProductDefaultQty($product, $productBlock)
    {
        $qty = $productBlock->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }
    
    public function getQuantityValidators()
    {
        $validators = [];
        $validators['required-number'] = true;
        return $validators;
    }
    
    public function getMagentoVersion()
    {
        if ($this->_magentoVersion === null) {
            $this->_magentoVersion = $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
        }
        return $this->_magentoVersion;
        
    }
    
    public function isMagento23x()
    {
        $version = $this->getMagentoVersion();
        return (version_compare($version, '2.3.0', '>=') ||  version_compare($version, '2.3.0-dev', '>='));
    }
    
    public function toCamelCase($string, $capitaliseFirstChar = false)
    {
        if ($capitaliseFirstChar) {
            return \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($string);
        } else {
            return \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToCamelCase($string);
        }
    }
    
    public function getProductCustomTabsXml()
    {
        if ($product = $this->_coreRegistry->registry('current_product')) {
            $customTabAttributeCodes = $this->getConfig('pages/product_view/custom_tab_attributes');
            if ($customTabAttributeCodes) {
                $customTabAttributeCodes = explode(',', $customTabAttributeCodes);
                $xml = '<body><referenceBlock name="product.info.details">';
                foreach ($customTabAttributeCodes as $code) {
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
    }
    
    public function addProductCustomTabs($layout)
    {
        if ($product = $this->_coreRegistry->registry('current_product')) {
            $tabs = $layout->getBlock('product.info.details');
            if ($tabs) {
                $customTabAttributeCodes = $this->getConfig('pages/product_view/custom_tab_attributes');
                if ($customTabAttributeCodes) {
                    $customTabAttributeCodes = explode(',', $customTabAttributeCodes);
                    foreach ($customTabAttributeCodes as $code) {
                        $attribute = $product->getResource()->getAttribute($code);
                        if ($attribute && $attribute->getId()) {
                            $label = $attribute->getData('store_label');
                            $blockName = 'product-tab-'.$code;
                            
                            $tab = $layout->createBlock('Magento\Catalog\Block\Product\View\Description', $blockName)
                                ->addData([
                                    'at_call'       => 'get'.$this->toCamelCase($code, true),
                                    'at_code'       => $code,
                                    'css_class'     => 'custom-tab '.$code,
                                    'at_label'      => 'none',
                                    'title'         => $label
                                ])->setTemplate('Magento_Catalog::product/view/attribute.phtml');
                            $layout->setChild('product.info.details', $blockName, $blockName);
                            $layout->addToParentGroup($blockName, 'detailed_info');
                        }
                    }
                }
                
                $customTabCMSBlock = $this->getConfig('pages/product_view/custom_tab_block');
                if ($customTabCMSBlock) {
                    $customTabCMSBlock = explode(',', $customTabCMSBlock);
                    foreach ($customTabCMSBlock as $blockIdentifier) {
                        $block = $this->_objectManager->get('Magento\Cms\Model\BlockFactory')
                            ->create()
                            ->setStoreId($this->_storeId)
                            ->load($blockIdentifier, 'identifier');
                            
                        if ($block->getId()) {
                            $blockName = 'product-tab-cms-' . $blockIdentifier;
                            $label = $block->getTitle();
                            $tab = $layout->createBlock('Magento\Cms\Block\Block', $blockName)
                                ->addData([
                                    'title'         => $label,
                                    'block_id'      => $block->getId(),
                                ]);
                            $layout->setChild('product.info.details', $blockName, $blockName);
                            $layout->addToParentGroup($blockName, 'detailed_info');
                        }
                    }
                }
            }
            /* custom attribute */
            if ($customAttributes = $this->getConfig('pages/product_view/custom_attribute')) {
                $customAttributes = json_decode($customAttributes, true);
                foreach ($customAttributes as $i => $row) {
                    $isTab = $row['template'] === 'tab';
                    if ($isTab) {
                        $parent = 'product.info.details';
                    } else {
                        $parent = $row['parent'];
                    }
                    $canDisplay = $parent && $row['code'];
                    if ($row['product_type']) {
                        $canDisplay = ($product->getTypeId() === $row['product_type']);
                    }
                    $canDisplay &= $layout->isBlock($parent) ? true : $layout->isContainer($parent);
                    if ($canDisplay) {
                        $code = $row['code'];
                        $label = $row['label'];
                        $template = $isTab ? 'Codazon_ThemeLayoutPro::catalog/product/custom-attribute/tab.phtml' : $row['template'];
                        $blockName = 'cdz-product-attribute-' . $code . '-' . $i;
                        $block = $layout->createBlock(\Magento\Catalog\Block\Product\View\Description::class, $blockName)
                            ->addData([
                                'at_call'       => 'get'.$this->toCamelCase($code, true),
                                'at_code'       => $code,
                                'css_class'     => ' custom-attr '.$code . ( $row['css_class'] ? ' '.$row['css_class']:''),
                                'at_label'      => $isTab ? 'none' : ($label ? $label : 'default'),
                                'at_type'       => $row['type'],
                                'title'         => $label
                            ])->setTemplate($template);
                        $layout->setChild($parent, $blockName, $blockName);
                        if ($isTab) {
                            $layout->addToParentGroup($blockName, 'detailed_info');
                        }
                        if ($row['position'] && $row['sibling']) {
                            $layout->reorderChild($parent, $blockName, $row['sibling'], ($row['position'] === 'after'));
                        }
                    }
                }
            }
        }
    }
    
    public function getCurrentStoreId()
    {
        return $this->_storeId;
    }
    
    public function getObject($class)
    {
        return $this->_objectManager->get($class);
    }
    
    public function createObject($class, $params)
    {
        return $this->_objectManager->create($class, $params);
    }
    
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
    
    public function minifyHtml($content)
    {
        return $this->_objectManager->get(\Codazon\Core\Helper\Data::class)->minifyHtml($content);
    }
    
    public function enableProductLazyImg()
    {
        if ($this->productLazyImg === null) {
            $this->productLazyImg = (bool)$this->getConfig('images/general/lazyload_for_product_images');
        }
        return $this->productLazyImg;
    }
    
    public function getProductImageHtml($product, $width, $height, $imageHelper, $block)
    {
        if ($this->enableProductLazyImg()) {
            $mainImage = $imageHelper->init($product, 'category_page_grid')->setImageFile($product->getData('small_image'));
            $mainImage = '<img data-hasoptions=\''.($product->getHasOptions()? '1':'0').'\' class="product-image-photo main-img" data-lazysrc="'.$mainImage->resize($width, $height)->getUrl().'" alt="'. ($label = $block->escapeHtmlAttr($mainImage->getLabel())) .'" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAoMBgDTD2qgAAAAASUVORK5CYII=" />';
            $hoveredImage = $imageHelper->init($product, 'category_page_grid')->setImageFile($product->getData('thumbnail'));
            $hoveredImage = '<img class="product-image-photo hovered-img" data-lazysrc="'.$hoveredImage->resize($width, $height)->getUrl().'" alt="'.$label.'" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAoMBgDTD2qgAAAAASUVORK5CYII=" />';
        } else {
            $mainImage = $imageHelper->init($product, 'category_page_grid')->setImageFile($product->getData('small_image'));
            $mainImage = '<img data-hasoptions=\''.($product->getHasOptions()? '1':'0').'\' class="product-image-photo main-img" src="'.$mainImage->resize($width, $height)->getUrl().'" alt="'. ($label = $block->escapeHtmlAttr($mainImage->getLabel())) .'" />';
            $hoveredImage = $imageHelper->init($product, 'category_page_grid')->setImageFile($product->getData('thumbnail'));
            $hoveredImage = '<img class="product-image-photo hovered-img" src="'.$hoveredImage->resize($width, $height)->getUrl().'" alt="'.$label.'" />';
        }
        return $mainImage . $hoveredImage;
    }
    
    
    public function getCurrentCategory()
    {
        if ($this->currentCategory === null) {
            $this->currentCategory = $this->_coreRegistry->registry('current_category');
        }
        return $this->currentCategory;
    }
    
    public function getProductImageSize($direction)
    {
        $size = null;
        $category = $this->getCurrentCategory();
        if ($category) {
            $size = (int)$category->getData('cdz_pimg_' . $direction);
        }
        return $size ? : ($this->getConfig('images/category/product_image_' . $direction)? : 150);
    }
    
    public function getSwatchImageSizeData($block = null)
    {
        if ($this->swatchImageSize === null) {
            $this->swatchImageSize = $block ? json_decode($block->getJsonSwatchSizeConfig(), true) : [];
            $this->swatchImageSize = array_replace_recursive($this->swatchImageSize, [
                'swatchImage' => [
                    'width' => (float)$this->getConfig('images/product/swatch_image_width'),
                    'height' => (float)$this->getConfig('images/product/swatch_image_height')
                ], 'swatchThumb' => [
                    'width' => (float)$this->getConfig('images/product/swatch_thumb_width'),
                    'height' => (float)$this->getConfig('images/product/swatch_thumb_height')
                ]
            ]);
        }
        return $this->swatchImageSize;
    }
}
