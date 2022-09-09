<?php
namespace Codazon\Shopbybrandpro\Block\Adminhtml\System\Config;
  
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\Data\Form\Element\AbstractElement;
  
class CustomRoute extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {        
        $curTmpl = $this->getTemplate();
        $this->setTemplate('Codazon_Shopbybrandpro::config/custom-route.phtml');
        $this->setFormElement($element);
        $custom = $this->toHtml();
        $this->setTemplate($curTmpl);
        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr><tr><td colspan="4">' . $custom . '</td></tr>';
    }
}