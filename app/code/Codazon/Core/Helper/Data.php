<?php
/**
* Copyright © 2020 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Core\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $objectManager;
    
    protected $coreRegistry;
    
    protected $storeManager;
    
    protected $context;
    
    protected $scopeConfig;
    
    protected $storeId;
    
    protected $layout;
    
    protected $pageConfig;
    
    protected $request;
    
    protected $blockFilter;
    
    protected $inlineHtmlTags = [
        'b',
        'big',
        'i',
        'small',
        'tt',
        'abbr',
        'acronym',
        'cite',
        'code',
        'dfn',
        'em',
        'kbd',
        'strong',
        'samp',
        'var',
        'a',
        'bdo',
        'br',
        'img',
        'map',
        'object',
        'q',
        'span',
        'sub',
        'sup',
        'button',
        'input',
        'label',
        'select',
        'textarea',
        '\?',
    ];
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storeManager = $storeManager;
        $this->storeId = $storeManager->getStore()->getId();
        $this->pageConfig = $pageConfig;
    }
    
    public function getObjectManager()
    {
        return $this->objectManager;
    }
    
    public function getStoreManager()
    {
        return $this->storeManager;
    }
    
    public function getCurrentStoreId()
    {
        return $this->storeId;
    }
    
    public function getLayout()
    {
        if ($this->layout === null) {
            $this->layout = $this->objectManager->get(\Magento\Framework\View\Layout::class);
        }
        return $this->layout;
    }
    
    public function getPageConfig()
    {
        return $this->pageConfig;
    }
    
    public function getUrl($path = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($path, $params);
    }
    
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
    
    public function getRequest()
    {
        return $this->_request;
    }
    
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeId);
    }
    
    public function getMediaUrl($path = '')
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;   
    }
    
    public function getBlockFilter()
    {
        if ($this->blockFilter === null) {
            $this->blockFilter = $this->objectManager->get(\Magento\Cms\Model\Template\FilterProvider::class)->getBlockFilter();
        }
        return $this->blockFilter;
    }
    
    public function htmlFilter($content)
    {
        return $this->getBlockFilter()->filter((string)$content);
    }
    
    public function minifyHtml($content, $minifyJs = true)
    {
        //Storing Heredocs
        $heredocs = [];
        $content = str_replace("//", "23abc22", (string)$content);
        $content = preg_replace_callback(
            '/<<<([A-z]+).*?\1;/ims',
            function ($match) use (&$heredocs) {
                $heredocs[] = $match[0];

                return '__MINIFIED_HEREDOC__' .(count($heredocs) - 1);
            },
            $content
        );
        $inlineTags = implode('|', $this->inlineHtmlTags);
        $content = preg_replace(
            '#(?<!]]>)\s+</#',
            '</',
            preg_replace(
                '#((?:<\?php\s+(?!echo|print|if|elseif|else)[^\?]*)\?>)\s+#',
                '$1 ',
                preg_replace(
                    '#(?<!' . $inlineTags . ')\> \<#',
                    '><',
                    preg_replace(
                        '#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre|script)\b))*+)'
                        . '(?:<(?>textarea|pre|script)\b|\z))#',
                        ' ',
                        preg_replace(
                            '#(?<!:|\\\\|\'|")//(?!\s*\<\!\[)(?!\s*]]\>)[^\n\r]*#',
                            '',
                            preg_replace(
                                '#(?<!:|\'|")//[^\n\r]*(\?\>)#',
                                ' $1',
                                preg_replace(
                                    '#(?<!:)//[^\n\r]*(\<\?php)[^\n\r]*(\s\?\>)[^\n\r]*#',
                                    '',
                                    preg_replace(
                                    '# ? (</(' . $inlineTags . ')>)#',
                                    '$1 ', $content)
                                )
                            )
                        )
                    )
                )
            )
        );

        //Restoring Heredocs
        $content = preg_replace_callback(
            '/__MINIFIED_HEREDOC__(\d+)/ims',
            function ($match) use ($heredocs) {
                return $heredocs[(int)$match[1]];
            },
            $content
        );
        $content = str_replace("23abc22", "//", $content);
        if ($minifyJs) {
            $content = preg_replace_callback(
                '#<script(.*?)>(.*?)</script>#is', function ($matches) {
                    if (strpos($matches[1], 'x-magento-template') === false) {
                        try {
                            return  '<script'.$matches[1].'>'.\JShrink\Minifier::minify($matches[2]).'</script>';
                        } catch (\Exception  $e) {
                            return $matches[0];
                        }
                    } else {
                        return $matches[0];
                    }
                },
                $content
            ) ?  : $content;
        }
        
        return rtrim($content);
    }
    
    
    public function getCoreRegistry()
    {
        return $this->coreRegistry;
    }
}
