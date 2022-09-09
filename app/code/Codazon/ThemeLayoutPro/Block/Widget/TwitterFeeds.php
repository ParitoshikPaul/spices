<?php

/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Block\Widget;

use Codazon\ThemeLayoutPro\Framework\Twitter\TwitterAPIExchange;

class TwitterFeeds extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    const OAUTH_ACCESS_TOKEN        = '3254498521-TgBCkqmPV3fmzj1METkNltwWsqzJ7e7F5Uxssx6';
    const OAUTH_ACCESS_TOKEN_SECRET = 'VoPYLnDvqmuqszxJNOgMVIWH4HXjrkQb71cA9z89kmRQP';
    const CONSUMER_KEY              = '0I49KYDWHSeEPMKVM1hp4RIVa';
    const CONSUMER_SECRET           = 'Ou0yGsj4Sn6zHgbO6xG64b6N4K4l2Z4t0ublx9kbmTxDWBbP9C';
    const TWITTER_URL               = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    
    protected $_template = 'Codazon_ThemeLayoutPro::widget/twitterfeeds.phtml';
    
    protected $sweet;
    
    protected $helper;
    
    protected $_defaultData = [
        'page_url'      => 'https://www.facebook.com/facebook',
        'hide_cover'    => 0,
        'show_facepile' => 1
    ];
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Codazon\ThemeLayoutPro\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => ['CDZ_TWITTER_FEED']
        ]);
    }
    
    public function getLastestTweets()
    {
        if ($this->sweet === null) {
            $username = $this->getData('user_name') ? $this->getData('user_name') : 'twitter';
            $limit = (int)$this->getData('limit') ? : 2;
            $this->sweet = $this->getLastestTweetsByUserName($username, $limit);
            if ($this->sweet) {
                $this->sweet = json_decode($this->sweet);
                if (!empty($this->sweet->errors)) {
                    $this->sweet = false;
                }
            }
        }
        return $this->sweet;
    }
    
    public function getLastestTweetsByUserName($username = 'twitter', $limit = 2)
    {
        $username     = trim((string)$username);
        $limit        = intval($limit);
        $tweets       = [];
        
        $settings = [
            'oauth_access_token'        => $this->helper->getScopeConfig('themelayoutpro/twitter/oauth_access_token'),
            'oauth_access_token_secret' => $this->helper->getScopeConfig('themelayoutpro/twitter/oauth_access_token_secret'),
            'consumer_key'              => $this->helper->getScopeConfig('themelayoutpro/twitter/consumer_key'),
            'consumer_secret'           => $this->helper->getScopeConfig('themelayoutpro/twitter/consumer_secret')
        ];
        foreach ($settings as $value) {
            if (!$value) {
                return false;
            }
        }
        
        $username = str_replace('@', '', $username);
        if (strtolower($username) === 'home') {
            $url = self::TWITTER_URL;
            $get = '?count=$limit';
        } else {
            $url = self::TWITTER_URL;
            $get = "?screen_name=$username&count=$limit";
        }

        try {
            $twitterAPIExchange = TwitterAPIExchange::class;
            $twitter = new $twitterAPIExchange($settings);
            $response = $twitter->setGetfield($get)
                ->buildOauth($url, 'GET')
                ->performRequest();
            return $response;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    public function dateFormat($date, $format)
    {
        $date = date_create($date);
        return date_format($date, $format);
    }
    
    public function getTemplate()
    {   
        if($this->getData('custom_template')){
            return $this->getData('custom_template');
        }else{
            return $this->_template;
        }
    }
    
    public function getCacheKeyInfo()
    {
        $instagram = serialize($this->getData());
        return [
            'CDZ_TWITTER_FEED',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            md5(json_encode($this->getData())),             
            $instagram
        ];
    }
}