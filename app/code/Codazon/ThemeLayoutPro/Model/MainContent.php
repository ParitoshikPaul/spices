<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model;

class MainContent extends \Codazon\ThemeLayoutPro\Model\ThemeLayoutAbstract
{
    const ENTITY = 'themelayout_maincontent';
    
    const CACHE_TAG = 'themelayout_maincontent';
    
    protected $_projectPath = 'codazon/themelayout/main';
    protected $_mainFileName = 'main-styles.less.css';
    protected $_cssFileName = 'main-styles.css';
    protected $elementType = 'main';
    protected $primary = 'entity_id';
    protected $_defaultValues = null;
    private $scopeOverriddenValue = null;
    protected $_flexibleLessDir = 'codazon/themelayout/main/general/flexible';
    protected $_storeValuesFlags = [];
    const KEY_IS_USE_DEFAULT = 'is_use_default';
    
    protected function _construct()
    {
        $this->_init('Codazon\ThemeLayoutPro\Model\ResourceModel\MainContent');
    }
        
    private function getAttributeScopeOverriddenValue()
    {
        if ($this->scopeOverriddenValue === null) {
            $this->scopeOverriddenValue = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Codazon\ThemeLayoutPro\Model\Eav\ScopeOverriddenValue');
        }
        return $this->scopeOverriddenValue;
    }
    
    public function getResourceCollection()
    {
        $collection = parent::getResourceCollection()->setStoreId($this->getStoreId());
        return $collection;
    }
    
    public function setExistsStoreValueFlag($attributeCode)
    {
        $this->_storeValuesFlags[$attributeCode] = true;
        return $this;
    }
    
	public function getExistsStoreValueFlag($attributeCode)
    {
        return array_key_exists($attributeCode, $this->_storeValuesFlags);
    }
    
    public function updateWorkspace($export = false)
    {
        parent::updateWorkspace($export);
        $customFields = $this->_getDecodedCustomFields();
        $this->updateWorkspaceForSecondaryStyles('blog-styles', $customFields);
        $this->updateWorkspaceForSecondaryStyles('product-view-styles', $customFields, ['product_view_less', 'product_view_custom_less']);
        return $this;
    }
    
    protected function _getSecondaryFileContent($styleName, $customFields, $flexStyles = [])
    {
        $content = "@import (optional,less)'../_default_variables.less.css';\n";
        $content .= "@import (less)'" . $this->_varFileName . "';\n";
        $content .= "@import (less)'../_{$styleName}.less.css';\n";
        if (count($flexStyles)) {
            $usedLess = [];
            foreach ($flexStyles as $style) {
                if (!empty($customFields[$style])) {
                    $usedLess[] = $customFields[$style];
                }
            }
            $usedLess = array_unique($usedLess);
            if (count($usedLess)) {
                foreach ($usedLess as $flexibleLess) {
                    $content .= "@import (optional,less)'../general/flexible/" . $flexibleLess .  "';\n";
                }
            }
        }
        return $content;
    }
    
    public function updateWorkspaceForSecondaryStyles($styleName, $customFields = [], $flexStyles = [], $attachMainStyle = true)
    {
        /* LESS files */
        $elDirName = $this->_getElementDirName();
        $elDir = $this->_projectDir . $elDirName . '/';
        $elMainFile = $elDir . "_{$styleName}.less.css";
        $elVarFile = $elDir . $this->_varFileName;
        $this->io->write($elMainFile, $this->_getSecondaryFileContent($styleName, $customFields, $flexStyles), 0666);
        $parser = new \Less_Parser(
            [
                'relativeUrls' => false,
                'compress' => true
            ]
        );
        $content = $this->io->read($elMainFile);       
        /* CSS files */
        $elCssFile = $elDir . "{$styleName}.css";
        $rtlElCssFile = $elDir . "rtl-{$styleName}.css";
        $this->io->write($elCssFile, $content, 0666);
        
        try {
            gc_disable();
            $parser->parseFile($elCssFile, '');
            $content = $parser->getCss();
            gc_enable();
            
            $content = str_replace($this->_imagesPath, '../../../../' . $this->_imagesPath, $content);
			$content = str_replace($this->_fontPath, '../../../../' . $this->_fontPath, $content);
            
            $normalContent = $this->_objectManager->get(\Codazon\ThemeLayoutPro\Helper\CssManager::class)
                ->removeRtlCss($content);
            if ($attachMainStyle) {
                $normalContent .= $this->io->read($elDir . "main-styles.css");
                $content .= $this->io->read($elDir . "rtl-main-styles.css");
            }            
            $this->io->write($elCssFile, $normalContent, 0666);
            $this->io->write($rtlElCssFile, $content, 0666);
        } catch(\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
        return $this;
    }
    
    public function getSecondaryCssFile($styleName, $rtl = false)
    {
        return $rtl ? $this->_projectPath .'/'. $this->_getElementDirName() . "/rtl-{$styleName}.css" : $this->_projectPath .'/'. $this->_getElementDirName() . "/{$styleName}.css";
    }
}