<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Block;

use Magento\Customer\Model\Context;

class Header extends LayoutAbstract
{
    public function getElementHtml()
    {
        $header = $this->helper->getHeader();
		$content = json_decode($header->getContent(), true);
		$type = $this->getData('content_type') ? : 'content_1';
		$content = $content[$type];
        if ($content) {
            return $this->filter($content);
        } else {
            return '';
        }
    }
}