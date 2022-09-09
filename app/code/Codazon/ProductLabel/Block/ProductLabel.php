<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\ProductLabel\Block;

class ProductLabel extends \Magento\Framework\View\Element\Template
{
	protected $objectManager;

    protected $_template = 'productlabel.phtml';

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = []
    ) {
		$this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        parent::__construct($context, $data);
    }
    
    public function getObjectManager()
    {
        return $this->objectManager;
    }
    
    public function getHtml()
    {
        return $this->_toHtml();
    }
}
