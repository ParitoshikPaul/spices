<?php
/**
 * Copyright © 2020 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout\Element;

class ProcessLayoutRenderElement implements ObserverInterface
{
    protected $helper;
    
    protected $enableMinify;
    
    public function __construct(
        \Codazon\ThemeLayoutPro\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    protected function canMinify()
    {
        if ($this->enableMinify === null) {
            $request = $this->helper->getRequest();
            $this->enableMinify = $this->helper->getScopeConfig('themelayoutpro/env/minify_html') && (!$request->getParam('is_amp_page')) && ($request->getFrontName() !== 'robots');
        }
        return $this->enableMinify;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->canMinify()) {
            $event = $observer->getEvent();
            $transport = $event->getTransport();
            $block = $event->getLayout()->getBlock($event->getElementName());
            if ($block instanceof \Magento\Framework\View\Element\AbstractBlock) {
                $output = $this->helper->minifyHtml($transport->getData('output'));
                $transport->setData('output', $output);
            }
        }
    }
}