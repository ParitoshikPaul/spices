<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Block\Adminhtml\Config\Form\Field;

class CustomAttribute extends \Magento\Config\Block\System\Config\Form\Field
{

    protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {        
        $curTmpl = $this->getTemplate();
        $this->setTemplate('Codazon_ThemeLayoutPro::config/custom-attribute.phtml');
        $this->setFormElement($element);
        $custom = $this->toHtml();
        $this->setTemplate($curTmpl);
        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr><tr><td colspan="4">' . $custom . '</td></tr>';
    }
}
