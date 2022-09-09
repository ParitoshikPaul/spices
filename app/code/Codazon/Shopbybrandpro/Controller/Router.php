<?php
namespace Codazon\Shopbybrandpro\Controller;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Codazon\Shopbybrandpro\Helper\Data as BrandHelper;

class Router implements \Magento\Framework\App\RouterInterface
{
    protected $actionFactory;

    protected $_storeManager;

    protected $_brandFactory;

    protected $_scopeConfig;
    
    protected $_attributeCode;
    
    protected $_helper;
    
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Codazon\Shopbybrandpro\Model\BrandFactory $brandFactory,
        \Codazon\Shopbybrandpro\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        $this->actionFactory = $actionFactory;
        $this->_brandFactory = $brandFactory;
        $this->_storeManager = $helper->getStoreManager();
        $this->_scopeConfig = $helper->getScopeConfig();
        $this->_attributeCode = $helper->getStoreBrandCode();
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        
        $pathInfo = trim($request->getPathInfo(), '/');
        $urlKey = explode('/', $pathInfo);
        $storeId = $this->_storeManager->getStore()->getId();
        $mainRoute = $this->_helper->getViewRoute();
        $routes = $this->_helper->getAllRoutes();
        $attributes = [$mainRoute => $this->_attributeCode];
        
        $customRoutes = $this->_helper->getCustomRoutesData();
        if ($customRoutes) {
            foreach ($customRoutes as $customRoute) {
                $attributes[$customRoute['route']] = $customRoute['code'];
            }
        }
        
        if (isset($urlKey[1]) && in_array($urlKey[0], $routes)) {
            $viewRoute = $urlKey[0];
            $code = $attributes[$viewRoute];
            $actionName = ($code === $this->_attributeCode) ? 'view' : 'attributeView';
            $urlKey = strtolower(urldecode($urlKey[1]));
            
            $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                        
            $brandCollection = $this->_brandFactory->create()
                ->getCollection();
            
            $optionValueTable = $brandCollection->getTable('eav_attribute_option_value');
            $select = $brandCollection->getConnection()->select();
            $select->from(['main_table' => $optionValueTable], ['option_id']);
            $select->joinLeft([ 'eao' => $brandCollection->getTable('eav_attribute_option') ], 'main_table.option_id = eao.option_id', ['attribute_id']);
            $select->joinLeft([ 'ea' => $brandCollection->getTable('eav_attribute') ], 'eao.attribute_id = ea.attribute_id', ['attribute_code']);
            $select->where('main_table.store_id IN ('.$defaultStoreId.', '.$storeId.')')
                ->where('LOWER(REPLACE(REPLACE(RTRIM(main_table.value), " ", "-"), "\'", "-")) = ?', $urlKey)
                ->where("ea.attribute_code = '{$code}'")
                ->order('main_table.store_id DESC')
                ->limit(1);
                
            $brand = $brandCollection->getConnection()->fetchRow($select);
            
            if ($brand) {
                $request->setModuleName('brands')
                    ->setControllerName('index')
                    ->setActionName($actionName)
                    ->setParam($code, $brand['option_id'])
                    ->setParam(BrandHelper::CURRENT_ATTR_PARAM, $code);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, "$viewRoute/$urlKey");
                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
            
            $brandCollection->setStore($storeId)
                ->addAttributeToFilter('brand_url_key', $urlKey);
            $brand = $brandCollection->getFirstItem();
            
            if ($brand->getId()) {
                $request->setModuleName('brands')
                    ->setControllerName('index')
                    ->setActionName($actionName)
                    ->setParam($code, $brand->getOptionId())
                    ->setParam(BrandHelper::CURRENT_ATTR_PARAM, $code);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, "$viewRoute/$urlKey");
                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
        } elseif (in_array($urlKey[0], $routes)) {
            $viewRoute = $urlKey[0];
            $code = $attributes[$viewRoute];
            $actionName = ($code === $this->_attributeCode) ? 'index' : 'attribute';
            $request->setModuleName('brands')
                ->setControllerName('index')
                ->setActionName($actionName)
                ->setParam(BrandHelper::CURRENT_ATTR_PARAM, $code);
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, "{$viewRoute}");
            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
        }
        return null;
    }
}
