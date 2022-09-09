<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Block\Adminhtml\Config\Form\Field;

class InstagramData extends \Magento\Config\Block\System\Config\Form\Field
{

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $curTmpl = $this->getTemplate();
        $html = $this->setTemplate('Codazon_ThemeLayoutPro::config/instagram-data.phtml')->toHtml();
        $this->setTemplate($curTmpl);
        return parent::_getElementHtml($element) . $html;
    }
}
