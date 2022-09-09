<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\ThemeLayoutPro\Controller\Ajax;

class MainWidget extends \Magento\Framework\App\Action\Action
{
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
        $this->getRequest()->setRequestUri('/');
        return $this->layoutFactory->create();
    }
}