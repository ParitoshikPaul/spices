<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product attribute add/edit form main tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Codazon\AjaxLayeredNavPro\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * @api
 * @since 100.0.2
 */
class ExtraOptions extends Generic
{
    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * @var PropertyLocker
     */
    private $propertyLocker;
    
    
    protected $extraStyles;
    
    protected $helper;
    
    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param PropertyLocker $propertyLocker
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        PropertyLocker $propertyLocker,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->propertyLocker = $propertyLocker;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->extraStyles = $this->objectManager->get('Codazon\AjaxLayeredNavPro\Model\Config\Source\AttributeExtraStyles');
        $this->helper = $this->objectManager->get('Codazon\AjaxLayeredNavPro\Helper\Data');
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();
        $this->helper->extractExtraOptions($attributeObject);
        
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        
        $fieldset = $form->addFieldset(
            'extra_options_fieldset',
            ['legend' => __('Extra Options'), 'collapsable' => true]
        );
        $yesnoSource = $this->yesNo->toOptionArray();
        $extraStyles = $this->extraStyles->toOptionArray();
        
        $fieldset->addField(
            'custom_style',
            'select',
            [
                'name' => 'custom_style',
                'label' => __('Custom Frontend Style'),
                'title' => __('Custom Frontend Style'),
                'values' => $extraStyles
            ]
        )->setData('value', $attributeObject->getData('custom_style'));
        $fieldset->addField(
            'not_seo',
            'select',
            [
                'name' => 'not_seo',
                'label' => __('Skip SEO'),
                'title' => __('Skip SEO'),
                'values' => $yesnoSource
            ]
        )->setData('value',  $attributeObject->getData('not_seo'));
        $this->setForm($form);
        $this->getPropertyLocker()->lock($form);
        return $this;
    }
    
    private function getPropertyLocker()
    {
        if (null === $this->propertyLocker) {
            $this->propertyLocker = ObjectManager::getInstance()->get(PropertyLocker::class);
        }
        return $this->propertyLocker;
    }
    
    /**
     * Retrieve attribute object from registry
     *
     * @return mixed
     */
    private function getAttributeObject()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }
}
