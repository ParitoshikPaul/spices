<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Block\Adminhtml\Config\Form\Field;

class Mapping extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    private $yesnoRenderer;

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('title', ['label' => __('Title')]);
        $this->addColumn('latitude', ['label' => __('Latitude')]);
        $this->addColumn('longitude', ['label' => __('Longitude')]);
        $this->addColumn('address', ['label' => __('Address')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function getYesnoRenderer()
    {
        if (!$this->yesnoRenderer) {
            $this->yesnoRenderer = $this->getLayout()->createBlock(
                \Magento\Config\Block\System\Config\Form\Field\Yesno::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->yesnoRenderer->setClass('overwrite_status');
        }
        return $this->yesnoRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        //$optionExtraAttr['option_' . $this->getYesnoRenderer()->calcOptionHash($row->getData('overwrite_status'))] = 'selected="selected"';

        $row->setData('option_extra_attrs',$optionExtraAttr);
    }
}
