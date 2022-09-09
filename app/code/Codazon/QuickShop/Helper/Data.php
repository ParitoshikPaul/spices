<?php
/**
 * Copyright © 2015 Codazon . All rights reserved.
 */
namespace Codazon\QuickShop\Helper;
use Magento\Framework\App\Filesystem\DirectoryList;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_storeManager;
	
    protected $scopeConfig;
	
    protected $_scopeStore;
	
    protected $_qsConfig = [];
    
    protected $_quickShopLabel = '';
    
    protected $_store;
    
    const WIDGET_PATH = 'Codazon_QuickShop/js/quickshop';
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		parent::__construct($context);
		$this->_scopeStore =  \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$this->_storeManager = $storeManager;
        $this->_quickShopLabel = $this->scopeConfig->getValue('quickshop/general/label', $this->_scopeStore);
        $this->_store = $this->_storeManager->getStore();
	}
    
    public function getWidgetOptions($_product)
    {
        if (!$this->_qsConfig) {   
            $this->_qsConfig = [self::WIDGET_PATH => [
                'baseUrl'        => $this->getBaseUrl(),
            ]];
        }
        $this->_qsConfig[self::WIDGET_PATH]['qsUrl'] =  $this->getQuickViewUrl($_product);
        return $this->_qsConfig;
    }
    
	public function getQuickShopButton($_product, $class = '')
    {
        $widgetOption = $this->getWidgetOptions($_product);
		$html = "";
		if($this->scopeConfig->getValue('quickshop/general/active',$this->_scopeStore)){
			$quickShopLabel = $this->getQuickShopLabel();
			$html  = "<a class=\"qs-button {$class}\" href=\"javascript:void(0)\" data-mage-init='" . json_encode($widgetOption) ."' title=\"{$quickShopLabel}\">";
			$html .= "<span><span>{$quickShopLabel}</span></span>";
			$html .= "</a>";
		}
		return $html;
	}
	
    public function getBaseUrl()
    {
		return $this->_storeManager->getStore()->getBaseUrl();
	}
	
    public function getQuickViewUrl($_product)
    {
		$id = $_product->getId();
		return $this->_store->getUrl('quickview/index/view/id/' . $id);
	}
	
    public function getConfig($fullPath){
		return $this->scopeConfig->getValue($fullPath,$this->_scopeStore);
	}
	
    public function getQuickShopLabel() {
		return $this->_quickShopLabel;
	}
	
    public function getPostDataParams($product, $refererUrl = null)
    {
		$data['product'] = $product->getId();
		if($this->_getRequest()->getServer('HTTP_REFERER')){
			$data[\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED] = base64_encode($this->_getRequest()->getServer('HTTP_REFERER'));
        }else{
			$data[\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED] = base64_encode($this->_getUrl(''));
		}
		return json_encode(['action' => $this->_getUrl('catalog/product_compare/add'), 'data' => $data]);
    }
}
