<?php
/**
 * Copyright © Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\GoogleAmpManager\Plugin\App\Response;

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
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Codazon\Core\Helper\Data::class);
        if ($originalContent = $helper->getCoreRegistry()->registry('amp_content_output')) {
            $subject->setContent($originalContent);
        } else {
            /* $origin = $helper->getRequest()->getHeader('Origin');
            if ($origin && (stripos($origin, 'cdn.ampproject.org') !== false)) {
                $subject->setHeader('Access-Control-Allow-Origin', $origin, true);
                $subject->setHeader('Access-Control-Allow-Credentials', 'true', true);
            } elseif ($helper->getRequest()->getParam('__amp_source_origin')) {
                $subject->setHeader('Access-Control-Allow-Origin', '*', true);
            } */
        }
        $proceed();
    }
}
