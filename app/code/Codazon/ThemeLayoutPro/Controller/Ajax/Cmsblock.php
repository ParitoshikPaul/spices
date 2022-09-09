<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\ThemeLayoutPro\Controller\Ajax;

class Cmsblock extends \Magento\Framework\App\Action\Action
{
    protected $block;
    
	protected $helper;
    
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Cms\Model\Block $block,
        \Codazon\Core\Helper\Data $helper
    ) {
		$this->block = $block;
        $this->helper = $helper;
		parent::__construct($context);
		
    }
    
    public function execute()
    {
        if ($identifier = $this->getRequest()->getParam('block_identifier')) {
            $block = $this->block->load($identifier, 'identifier');
            if ($block->getId()) {
                $this->getResponse()->setBody($this->helper->minifyHtml($this->helper->htmlFilter($block->getContent())));
            }
        }
    }
}