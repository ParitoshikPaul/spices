<?php
/**
 * Copyright © Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Plugin\App\Response;

use Magento\Framework\App\Response\Http as ResponseHttp;


class Http
{
    /**
     * Plugin
     *
     * @param \Magento\Catalog\Controller\Category\View $controller
     * @param \Closure $proceed
     */
    
    public function aroundSendResponse(
        ResponseHttp $subject,
        \Closure $proceed
    ) {
        /* $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Codazon\ThemeLayoutPro\Helper\Data::class);
        if ($helper->getScopeConfig('themelayoutpro/env/minify_html') && !($helper->isAdmin())) {
            $content = (string)$subject->getContent();
            if (strrpos($content, '</body') !== false) {
                $subject->setContent($helper->minifyHtml($subject->getContent()));
            }
        } */
        $proceed();
    }
}
