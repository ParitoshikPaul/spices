<?php
/**
 * Copyright © Codazon 2019, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Plugin\Controller;

use Codazon\Core\Helper\Data as CoreHelper;

class CatalogSearchAdvancedResult
{
    protected $helper;
    
    public function __construct(
        CoreHelper $helper
    ) {
        $this->helper = $helper;
    }
    
    public function afterExecute(
        \Magento\CatalogSearch\Controller\Advanced\Result $controller,
        $resultRedirect = false
    ) {
        if ($this->helper->getRequest()->getParam('cdz_ajax_advanced_search')) {
            $result['success'] = true;
            $objectManager = $this->helper->getObjectManager();
            if ($resultRedirect instanceof \Magento\Framework\Controller\Result\Redirect\Interceptor) {
                $result['success'] = false;
                $result['message'] = $objectManager->get(\Magento\Framework\Message\Manager::class)->getMessages()->getLastAddedMessage()->getText();
            } else {
                $list = $this->helper->getLayout()->getBlock('search_result_list');
                if (!$list->getLoadedProductCollection()->count()) {
                    $result['success'] = false;
                    $result['message'] = __('We can\'t find any items matching these search criteria.');
                }
            }
            return $objectManager->create(\Magento\Framework\Controller\Result\JsonFactory::class)->create()->setData($result);
        }
        return $resultRedirect;
    }
}