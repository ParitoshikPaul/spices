<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout\Element;

class LayoutSchemaAttributesObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        /* $layout = $observer->getEvent()->getLayout();
        $update = $layout->getUpdate();
        if (!$update->isLayoutDefined()) {
            $handles = $update->getHandles();  
            if (in_array('catalog_product_view', $handles) || in_array('checkout_cart_configure', $handles)) {
                $helper = \Magento\Framework\App\ObjectManager::getInstance()->get('\Codazon\ThemeLayoutPro\Helper\Data');
                $layoutXml = $helper->getProductCustomTabsXml();
                $update->addUpdate($layoutXml);
            }
        } */
        
        // $elementName = $observer->getEvent()->getElementName();

        // if($layout->isContainer($elementName) && ($containerTag = $layout->getElementProperty($elementName, Element::CONTAINER_OPT_HTML_TAG))) {
            // $nodes = $layout->getXpath(sprintf('//*[@name="%s"]/attribute[@name]', $elementName));
            // if ($nodes) {
                // /** @var \SimpleXMLElement $_node */
                // foreach ($nodes as $_node) {
                    // $name = $_node->attributes()->name;
                    // $value = $_node->attributes()->value;
                    // $output = $observer->getEvent()->getTransport()->getOutput();
                    // $output = preg_replace(
                        // "/^(<$containerTag.*?)(>)/",
                        // sprintf("$1 %s$2", ($name && $value) ? sprintf("%s=\"%s\"", $name, $value) : $name),
                        // $output
                    // );
                    // $observer->getEvent()->getTransport()->setOutput($output);
                // }
            // }
        // }
    }
}
