<?php
/**
 * Copyright © 2018 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\QuickShop\Controller\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class View extends \Magento\Catalog\Controller\Product
{
	protected $viewHelper;
    protected $resultForwardFactory;
    protected $resultPageFactory;
	protected $template;
	
	public function __construct(
		Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
		\Magento\Catalog\Helper\Product\View $viewHelper,
        PageFactory $resultPageFactory
		
    ) {
        $this->viewHelper = $viewHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
		parent::__construct($context);
    }
	

    public function execute()
    {
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');
        
        if ($curentUrl = $this->getRequest()->getParam('currentUrl')) {
            $this->getRequest()->setRequestUri($curentUrl);
            $this->getRequest()->setParam('currentUrl', null);
        }
        
        if (!$this->_request->getParam('___from_store')
            && $this->_request->isPost()
            && $this->_request->getParam(self::PARAM_NAME_URL_ENCODED)
        ) {
            $product = $this->_initProduct();
            if (!$product) {
                //return $this->noProductRedirect();
                return $this->noProductLoaded(__('Product not found.'));
            }
            if ($specifyOptions) {
                $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
                $this->messageManager->addNotice($notice);
            }
            if ($this->getRequest()->isAjax()) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode([
                        'backUrl' => $this->_redirect->getRedirectUrl()
                    ])
                );
                return;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
        }

        // Prepare helper and params
        $params = new \Magento\Framework\DataObject();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $page = $this->resultPageFactory->create(false, ['template' => 'Codazon_QuickShop::blank.phtml']);
            if ($this->getRequest()->getParam('ajaxcart_option')) {
                $page->addHandle(['quickshop_index_view_ajaxcart_option']);
            }
            $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
            return $page;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->noProductLoaded($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
	protected function noProductLoaded($message = '')
    {
		$html = '<div class="message info error"><div>'.$message.'</div></div>';
        $this->getResponse()->setBody($html);
    }	    
}
