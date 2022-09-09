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

class FirstLoad extends \Magento\Framework\App\Action\Action
{
   	const PAGE_VAR_NAME = 'np';
	
    protected $resultPageFactory;	
	
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
        if ($this->getRequest()->getParam('display_type') || $this->getRequest()->getParam('block_groups')) {
            $this->getRequest()->setRequestUri('/');
            return $this->layoutFactory->create();
        }
        return $this->resultRedirectFactory->create()->setPath('/');
	}
}
