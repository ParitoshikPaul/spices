<?php

/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Block\Widget;

class FacebookFeeds extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'Codazon_ThemeLayoutPro::widget/facebookfeeds.phtml';
    
    protected $_defaultData = [
        'page_url'      => 'https://www.facebook.com/facebook',
        'hide_cover'    => 0,
        'show_facepile' => 1
    ];
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => ['CDZ_FACEBOOK_FEED']
        ]);
    }
    
    public function getCacheKeyInfo()
    {
        $instagram = serialize($this->getData());
        return [
            'CDZ_FACEBOOK_FEED',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            md5(json_encode($this->getData())),             
            $instagram
        ];
    }
}