<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Block;

use Magento\Customer\Model\Context;

class Footer extends LayoutAbstract
{
    
    public function getElementHtml()
    {
        $footer = $this->helper->getFooter();
        $content = $footer->getContent();
        if ($content) {
            return $this->filter($content);
        } else {
            return '';
        }
    }
    
}