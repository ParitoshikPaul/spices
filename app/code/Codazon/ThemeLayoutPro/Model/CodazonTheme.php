<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model;

class CodazonTheme extends \Magento\Framework\DataObject
{
    const DEFAULT_THEME_CODE = 'Codazon/unlimited_default';
    
    const LIST_REGISTER_KEY = 'codazon_themes';
    
    protected $_themeFactory;
    
    protected $_themeList = false;
    
    protected $_defaultTheme = false;
    
    protected $_fileHelper;
    
    public function __construct(
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Codazon\Core\Helper\FileManager $fileHelper,
        array $data = []
    ) {
        $this->_themeFactory = $themeFactory;
        $this->_fileHelper = $fileHelper;
        parent::__construct($data);
    }
    
    public function getThemeList()
    {
        if ($this->_themeList === false) {
            $defautTheme = $this->getDefaultTheme();
            $this->_themeList = $this->_themeFactory->create()->getCollection()
                ->addFieldToFilter('area', 'frontend')
                ->addFieldToFilter(['parent_id', 'code'], [$defautTheme->getId(), self::DEFAULT_THEME_CODE])
                ->getItems();
        }
        return $this->_themeList;
    }
    
    public function getDefaultTheme() {
        if ($this->_defaultTheme === false) {
            $this->_defaultTheme = $this->_themeFactory->create()->load(self::DEFAULT_THEME_CODE, 'code');
        }
        return $this->_defaultTheme;
    }
    
    public function getThemeVersion()
    {
        $versionFile = $this->_fileHelper->getEtcXmlFilePath('version.txt', 'Codazon_ThemeLayoutPro');
        if ($this->_fileHelper->fileExists($versionFile)) {
            return $this->_fileHelper->read($versionFile);
        } else {
            return '';
        }
    }
}