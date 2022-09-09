<?php
/**
 * Copyright © 2018 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ShoppingCartPro\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class Crosssell extends \Magento\Framework\DataObject implements SectionSourceInterface
{
	protected $layoutFactory;
	
    protected $checkoutSession;
    
    protected $quote;
    
    protected $minimumAmountErrorMessage;
    
    /**
     * {@inheritdoc}
     */
	
    public function __construct(
		\Magento\Framework\View\Result\LayoutFactory $layoutFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($data);
		$this->layoutFactory = $layoutFactory;
        $this->checkoutSession = $checkoutSession;
    }
    
    public function getSectionData()
    {
		$layout = $this->layoutFactory->create(true);
        $layout->addHandle(['minicart_crosssell']);
        $pageLayout = $layout->getLayout();
        $items = [];
        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            $items[] = [
                'item_id'    => $item->getData('item_id'),
                'created_at'    => $item->getData('created_at'),
                'updated_at'    => $item->getData('updated_at')
            ];
        }
        /* if (!$pageLayout->getBlock('product.price.render.default')) {
            $pageLayout->createBlock(\Magento\Framework\Pricing\Render::class,
                    'product.price.render.default',
                    ['data' => ['price_render_handle' => 'catalog_product_prices', 'use_link_for_as_low_as' => true]]);
        } */
        return [
            'html' => $pageLayout->getOutput(),
            'items' => $items,
            //'validation_message' => $layout->getLayout()->createBlock(\Magento\Checkout\Block\Cart\ValidationMessages::class)->toHtml()
            'validation_message' => $this->getValidationMessage()
        ];
        
    }
    
    protected function getValidationMessage()
    {
        if (!$this->getQuote()->validateMinimumAmount()) {
            return '<div class="message message-notice notice"><div>' . $this->getMinimumAmountErrorMessage()->getMessage() . '</div></div>';
        } else {
            return '';
        }
    }
    
    
    protected function getMinimumAmountErrorMessage()
    {
        if ($this->minimumAmountErrorMessage === null) {
            $this->minimumAmountErrorMessage = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage::class
            );
        }
        return $this->minimumAmountErrorMessage;
    }
    
    
    protected function getAllQuoteItems()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote()->getAllVisibleItems();
        }
        return $this->getQuote()->getAllVisibleItems();
    }
    
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }
}