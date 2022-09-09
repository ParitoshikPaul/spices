<?php
/**
 * Product controller.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ProductFilter\Controller\Index;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Catalog\Model\Product as ModelProduct;

class AjaxLoad extends \Magento\Framework\App\Action\Action
{
   	const PAGE_VAR_NAME = 'np';
	protected $layoutFactory;	
	
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\LayoutFactory $layoutFactory
    ) {
		$this->layoutFactory = $layoutFactory;
		parent::__construct($context);
    }
   
    public function execute()
    {
        if ($this->getRequest()->getParam('action') !== 'ajaxload') {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('/');
        }
        $this->getRequest()->setRequestUri('/');
        $this->getResponse()->setHeader('Content-type','application/json');
		$layout = $this->layoutFactory->create();
        $result = json_decode($layout->getLayout()->getOutput(), true);
        $result['now'] = date("Y-m-d H:i:s");
        $this->getResponse()->setBody(json_encode($result));
	}
}
