<?php

/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Block\Widget;

class FlexibleBlock extends \Codazon\ThemeLayoutPro\Block\MainContent implements \Magento\Widget\Block\BlockInterface
{
    
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function _construct()
    {
        parent::_construct();
        $this->setNeedFilterHtml(true);
        $this->setTemplate('Codazon_ThemeLayoutPro::widget/flexibleblock.phtml');
        return $this;
    }
    
    public function getMainContent()
    {
        if ($this->_mainContentModel === false) {
            $identifier = $this->getData('block_identifier');
            $storeId = $this->_storeManager->getStore()->getId();
            $this->_mainContentModel = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Codazon\ThemeLayoutPro\Model\MainContent::class)
                ->getCollection()
                ->setStoreId($storeId)
                ->addFieldToFilter('identifier', $identifier)
                ->addFieldToFilter('is_active', 1)
                ->addAttributeToSelect('themelayout_content')
                ->getFirstItem();
        }
        return $this->_mainContentModel;
    }
    
    public function getCssUrl()
    {
        return str_replace(['https://', 'http://'], ['//', '//'], $this->getMediaUrl() . $this->getMainContent()->getMainCssFileRelativePath()) . '?version='. $this->getMainContent()->getVersion();
    }
    
}