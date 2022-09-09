<?php
/**
 * Copyright © 2021 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Block\Widget;

class CountdownTimer extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'Codazon_ThemeLayoutPro::widget/timer/timer-01.phtml';
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Codazon\ThemeLayoutPro\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);     
        $this->httpContext = $httpContext;     
        $this->helper = $helper;
    }    
}