<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Block\Adminhtml\Footer\Edit;
 
use Magento\Framework\Registry;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\UiComponent\Config\Reader;
use Magento\Framework\View\Element\UiComponentFactory;

class Variables extends \Magento\Framework\View\Element\AbstractBlock
{
    
    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param \Magento\Framework\Data\Form $form
     * @param array $data
     */
    protected $formName = 'themelayout_footer_form';
    
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Registry $registry,
        \Magento\Framework\Data\Form $form,
        UiComponentFactory $uiComponentFactory,
        $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->form = $form;
        $this->uiComponentFactory = $uiComponentFactory;
        parent::__construct($context, $data);
    }
    
    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getContentHtml();
        return $html;
    }
    
    /**
     * Prepares content block
     *
     * @return string
     */
    public function getContentHtml()
    {
        return '';
    }
    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getElementHtml();
    }
}

