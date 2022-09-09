<?php
/**
 * Copyright © 2020 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Lookbookpro\Block\Widget;

class FeaturedLooks extends \Codazon\Lookbookpro\Block\Item\AbstractItem implements \Magento\Widget\Block\BlockInterface
{
    protected $_lookCollection;
    
    protected $_template = 'widget/featured-looks/featured-looks-style-01.phtml';
    
    protected $_sliderData;
    
    public function _getCollection()
    {
        if ($this->_lookCollection === null) {
            $lookIds = $this->getData('look_ids');
            $lookbookIds = $this->getData('lookbook_ids');
            if ($lookIds) {
                if (is_string($lookIds)) {
                    $lookIds = explode(',', $lookIds);
                }
            }
            if ($lookbookIds) {
                if (is_array($lookbookIds)) {
                    $lookbookIds = implode(',', $lookbookIds);
                }
            }
            $this->_lookCollection = $this->_objectManager->get(\Codazon\Lookbookpro\Model\LookbookItem::class)->getCollection()->addAttributeToSelect('*');
            if ($lookIds) {
                $this->_lookCollection->addFieldToFilter('entity_id', ['in' => $lookIds])
                    ->getSelect()->order(new \Zend_Db_Expr("FIELD(e.entity_id, ". implode(',', $lookIds). ")"));
            }
            if ($lookbookIds) {
                $this->_lookCollection->getSelect()->joinLeft(
                    ['ccl' => $this->_lookCollection->getTable('cdzlookbook_item_group')],
                    'e.entity_id = ccl.item_id',
                    ['lookbook_id', 'position']
                )->group('e.entity_id')->where('ccl.lookbook_id in ('. $lookbookIds .')');
            }
            
        }
        return $this->_lookCollection;
    }
    
    public function getTemplate()
    {
        if ($this->getData('custom_template') != '') {
            return $this->getData('custom_template');
        } else {
            if (parent::getTemplate()) {
                return parent::getTemplate();
            } else {
                return $this->_template;
            }
        }
    }
    
    public function getLoadedCollection()
    {
        return $this->_getCollection();
    }
    
    public function getSliderData()
    {
        if (!$this->_sliderData) {
            $this->_sliderData = [
                'nav'           => (bool)$this->getData('slider_nav'),
                'dots'          => (bool)$this->getData('slider_dots'),
                'lazyLoad'      => true
            ];
            $adapts = array('1900', '1600', '1420', '1280','980','768','480','320','0');
            foreach ($adapts as $adapt) {
                 $this->_sliderData['responsive'][$adapt] = ['items' => (float)$this->getData('items_' . $adapt)];
            }
            $this->_sliderData['margin'] = (float)$this->getData('slider_margin');
        }
        return $this->_sliderData;
    }
}