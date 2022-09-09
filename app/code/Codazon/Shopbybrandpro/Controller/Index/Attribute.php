<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\Shopbybrandpro\Controller\Index;

use Magento\Framework\View\Result\PageFactory;

class Attribute extends \Magento\Framework\App\Action\Action
{
    protected $_scopeConfig;
    
    protected $_helper;
    
    protected $_attributeCode;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Codazon\Shopbybrandpro\Model\BrandFactory $brandFactory,
        \Codazon\Shopbybrandpro\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_scopeConfig = $helper->getScopeConfig();
        $this->_storeManager = $helper->getStoreManager();
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_brandFactory = $brandFactory;
        $this->_attributeCode = $helper->getCurrentAttributeCode() ? : $helper->getStoreBrandCode();
    }
    
    public function initBrands()
    {
        if (!$this->_coreRegistry->registry('brand_collection')) {
            $this->_coreRegistry->register('brand_collection', $this->_helper->getBrandCollection(
                null,
                $this->_helper->getAttributeIdByCode($this->_attributeCode),
                $this->_helper->getAttributesInList()
            ));
        }
        return $this->_coreRegistry->registry('brand_collection');
    }
    
    protected function _initBrandPage()
    {
        if (!$this->_coreRegistry->registry('all_brands_info')) {
            $brandData = $this->_helper->getCurrentAttributeData();
            $brands = new \Magento\Framework\DataObject([
                'attribute_code'            => $this->_attributeCode,
                'title'                     => $brandData['title'],
                'description'               => $brandData['description'],
                'display_featured_brands'   => (bool)$brandData['display_search'],
                'display_brand_search'      => (bool)$brandData['display_featured'],
                'meta_title'                => strip_tags($brandData['title']),
                'meta_keywords'             => $brandData['meta_keywords'],
                'meta_description'          => $brandData['meta_description'],
                'featured_brand_title'      => (string)$brandData['featured_title'] ? : __('Featured Options'),
                'all_title' => (string)$brandData['all_title'] ? : __('All Options'),
                'search_input_text' => (string)__('Search option here'),
                'no_result_text' =>  (string)__('No result matches your input')
            ]);
            $this->_coreRegistry->register('all_brands_info', $brands);
        }
        return $this->_coreRegistry->registry('all_brands_info');
    }
    
    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $brand = $this->_initBrandPage();
        $this->initBrands();
        $pageConfig = $page->getConfig();
        $pageConfig->addRemotePageAsset($this->_helper->getCoreHelper()->getCurrentUrl(),
            'canonical', 
            ['attributes' => ['rel' => 'canonical']]
        );
        $pageConfig->addBodyClass('cdz-all-brands');
        
        $title = $brand->getData('title');
        
        $pageConfig->getTitle()->set($brand->getData('meta_title')?:$title);
        $pageConfig->setKeywords($brand->getData('meta_keywords'));
        $pageConfig->setDescription($brand->getData('meta_description'));
        
        $pageMainTitle = $page->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }
        return $page;
    }
    
}