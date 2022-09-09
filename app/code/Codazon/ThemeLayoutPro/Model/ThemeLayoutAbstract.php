<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class ThemeLayoutAbstract extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    const CACHE_TAG = 'themelayout_abstractmodel';
    
    protected $_projectPath = 'codazon/themelayout/header';
    protected $_mainFileName = 'header-styles.less.css';
    protected $_cssFileName = 'header-styles.css';
    
    protected $elementType = 'header';
    protected $primary = 'header_id';
    
    
    protected $_varFileName = '_variables.less.css';
    protected $_elementsFileName = '_elements.less.css';
    
    protected $_tsprImg = "~'codazon/themelayout/images/tspr.png'";
    protected $_defaultFileName = 'variables.xml';
    protected $_imagesPath = 'codazon/themelayout/images';
	protected $_fontPath = 'codazon/themelayout/fonts';
    protected $defaultData = false;
    protected $initData = false;
    protected $_loadParent = false;
    protected $_autoImportLessFiles;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $_filesystem,
        \Magento\Framework\Filesystem\Io\File $io,
        array $data = []
    ) {
        parent::__construct($context, $registry, null, null, $data);
        $this->_filesystem = $_filesystem;
        $this->_dirHander = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->_mediaPath = $this->_dirHander->getAbsolutePath();
        $this->_projectDir = $this->_mediaPath . $this->_projectPath . '/';
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->io = $io;
    }
    
    public function getMainLessFileRelativePath()
    {
        return $this->_projectPath .'/'. $this->_getElementDirName() . '/' . $this->_mainFileName;
    }
        
    public function getMainCssFileRelativePath($rtl = false)
    {
        return $rtl ? $this->_projectPath .'/'. $this->_getElementDirName() . '/rtl-' . $this->_cssFileName : $this->_projectPath .'/'. $this->_getElementDirName() . '/' . $this->_cssFileName;
    }
    
    public function getMainLessFileAbsolutePath()
    {
        return $this->getProjectDir() . '/' . $this->_mainFileName;
    }
        
    public function getMainCssFileAbsolutePath()
    {
        return $this->getProjectDir() . '/' . $this->_cssFileName;
    }
    
    public function getProjectDir()
    {
        return $this->_projectDir . $this->_getElementDirName();
    }
    
    public function getElementsFileName()
    {
        return $this->_elementsFileName;
    }
    
    public function getVersion()
    {
        $customField = json_decode($this->getData('custom_fields'), true);
        return empty($customField['version']) ? '1' : $customField['version'];
    }
    
    public function cssFileExisted()
    {
        return $this->io->fileExists($this->getMainCssFileAbsolutePath(), true);
    }
    
    public function getAvailableStatuses()
	{
		return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}
        
    protected function _getElementDirName()
    {
        return $this->getData('identifier');
    }
    
    public function getDefaultData()
    {
        if ($this->defaultData === false) {
            $elDirName = $this->_getElementDirName();
            $elDir = $this->_projectDir . $elDirName . '/';
            $elDefaultFile = $elDir . $this->_defaultFileName;
            
            if ($this->io->fileExists($elDefaultFile, false)) {
                $xmlParser = $this->_objectManager->create('Magento\Framework\Xml\Parser');
                $xmlParser->load($elDefaultFile);
                $data = $xmlParser->xmlToArray();
                $data = $data['config'];
                unset($data['identifier']);
                unset($data['title']);
                $data['variables'] = json_encode($data['variables']);
                if (isset($data['custom_fields'])) {
                    foreach ($data['custom_fields'] as $name => $field) {
                        if (isset($data['custom_fields'][$name]['item'])) {
                            if (is_array($data['custom_fields'][$name]['item'][0])) {
                                foreach ($data['custom_fields'][$name]['item'][0] as $key => $value) {
                                    array_push($data['custom_fields'][$name]['item'], $value);
                                }
                                unset($data['custom_fields'][$name]['item'][0]);
                            }
                            $customField = [];
                            if (is_array($data['custom_fields'][$name]['item'])) {
                                foreach ($data['custom_fields'][$name]['item'] as $value) {
                                    $customField[] = $value;
                                }
                            } else {
                                $customField[] = $data['custom_fields'][$name]['item'];
                            }
                            $data['custom_fields'][$name] = $customField;
                        }
                    }
                    $data['custom_fields'] = json_encode($data['custom_fields']);
                }
                $this->defaultData = $data;
            } else {
                $this->defaultData = [];
            }
        }
        return $this->defaultData;
    }
    
    protected function _getDecodedCustomFields()
    {
        if ($customField = $this->getData('custom_fields')) {
            return json_decode($customField, true);
        } else {
            return [];
        }
    }

    public function updateWorkspace($export = false)
    {
        if (!file_exists($this->_projectDir)) {
            $this->io->mkdir($this->_projectDir, 0777, true);
        }
        $elDirName = $this->_getElementDirName();
        $elDir = $this->_projectDir . $elDirName . '/';
        $elMainFile = $elDir . $this->_mainFileName;
        $elVarFile = $elDir . $this->_varFileName;
        $elElementsFile = $elDir . $this->_elementsFileName;
        
        if (!$this->io->fileExists($elDir, false)) {
            $this->io->mkdir($elDir, 0777, true);
        }
        
        $this->io->write($elMainFile, $this->_getMainFileContent(), 0666);
        
        if (!$this->io->fileExists($elElementsFile, true)) {
            $this->io->write($elElementsFile, '', 0666);
        }
        $content = $this->_getVarFileContent() . "\n" . $this->getData('custom_variables');
        $this->io->write($elVarFile, $content, 0666);

        $parser = new \Less_Parser(
            [
                'relativeUrls' => false,
                'compress' => true
            ]
        );
        
        
        $content = $this->io->read($elMainFile);
        $customField = $this->_getDecodedCustomFields();
        if (!empty($customField['custom_less_code'])) {
            $content .= $customField['custom_less_code'];
        }
        
        $elCssFile = $elDir . $this->_cssFileName;
        $rtlElCssFile = $elDir . 'rtl-' . $this->_cssFileName;
        $this->io->write($elCssFile, $content, 0666);
        
        try {
            gc_disable();
            $parser->parseFile($elCssFile, '');
            $content = $parser->getCss();
            gc_enable();
            $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $mediaUrl =  $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            
            $content = str_replace($this->_imagesPath, '../../../../' . $this->_imagesPath, $content);
			$content = str_replace($this->_fontPath, '../../../../' . $this->_fontPath, $content);
            
            $normalContent = $this->_objectManager->get(\Codazon\ThemeLayoutPro\Helper\CssManager::class)
                ->removeRtlCss($content);
            if (!empty($customField['custom_css_code'])) {
                $content .= $customField['custom_css_code'];
                $normalContent .= $customField['custom_css_code'];
            }
            $this->io->write($elCssFile, $normalContent, 0666);
            $this->io->write($rtlElCssFile, $content, 0666);
        } catch(\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
        
        if ($export) {
            $elDefaultFile = $elDir . $this->_defaultFileName;
            $this->exportVariables($elDefaultFile);
        }
        return $this;
    }
    
    public function loadParentObject()
    {
        if (!$this->_loadParent) {
            if ($this->getData('parent')) {
                $parent = \Magento\Framework\App\ObjectManager::getInstance()->create(get_class($this));
                $parent->load($this->getData('parent'), 'identifier');
                $this->setData('parent_object', $parent);
            } else {
                $this->setData('parent_object', false);
            }
            $this->_loadParent = true;
        }
        return $this->getData('parent_object');
    }
    
    protected function exportVariables($file)
    {
        $xmlGenerator = $this->_objectManager->create('Magento\Framework\Xml\Generator');
        $variables = json_decode($this->getData('variables'), true);
        ksort($variables);
        $content = ['config' => [
            'identifier'        => $this->getData('identifier'),
            'title'             => $this->getData('title'),
            'variables'         => $variables,
            'layout_xml'        => $this->getData('layout_xml'),
            'custom_variables'  => $this->getData('custom_variables'),
            'custom_fields'     => json_decode($this->getData('custom_fields'), true),
            'parent'            => $this->getData('parent')
        ]];
        if ($this->getData('content')) {
            $content['config']['content'] = $this->getData('content');
        }
        if ($this->getData('themelayout_content')) {
            $content['config']['themelayout_content'] = $this->getData('themelayout_content');
        }
        /* if ($this->getData('themelayout_content')) {
            $content['config']['store_options'] = $this->getData('store_options');
        } */
        $xmlGenerator->arrayToXml($content)->save($file);       
    }
    
    public function getFlexibleLessDir()
    {
        return $this->_mediaPath . $this->_flexibleLessDir . '/';
    }
    
    public function getFlexibleFileList()
    {
        $lessDir = $this->getFlexibleLessDir();
        $lessFiles = array_filter(glob($lessDir . '*.less.css'), 'is_file');
        $list = [];
        foreach ($lessFiles as $lessfile) {
            $fileName = explode(DIRECTORY_SEPARATOR, $lessfile);
            $list[] = $fileName[count($fileName) - 1];
        }
        return $list;
    }
    
    protected $_useAsBlock;
    
    public function useAsBlock()
    {
        if ($this->_useAsBlock === null) {
            if ($customField = $this->getData('custom_fields')) {
                $customField = json_decode($customField, true);
                $this->_useAsBlock = isset($customField['use_as_block']) ? (bool)$customField['use_as_block'] : false;
            } else {
                $this->_useAsBlock = false;
            }
        }
        return $this->_useAsBlock;
    }
    
    protected function _getMainFileContent()
    {
        $this->autoImportLessFiles();
        $content = "@import (optional,less)'../_default_variables.less.css';\n";
        $content .= "@import (less)'" . $this->_varFileName . "';\n";
        
		if ($this->useAsBlock()) {
            $content .= "@import (less)'../_mini-general.less.css';\n";
        } else {
            $content .= "@import (less)'../_general.less.css';\n";
        }
        
        if ($customField = $this->getData('custom_fields')) {
            $customField = json_decode($customField, true);
            $usedLess = [];
            
            if (!empty($customField['flexible_less'])) {
                $usedLess = array_merge($usedLess, $customField['flexible_less']);
            }
            if (!$this->useAsBlock()) {
                if (!empty($customField['category_view_less'])) {
                    //$usedLess = array_merge($usedLess, [$customField['category_view_less']]);
                    $usedLess[] = $customField['category_view_less'];
                }
                /* if (!empty($customField['product_view_less'])) {
                    //$usedLess = array_merge($usedLess, [$customField['product_view_less']]);
                    $usedLess[] = $customField['product_view_less'];
                }
                if (!empty($customField['product_view_custom_less'])) {
                    //$usedLess = array_merge($usedLess, [$customField['product_view_custom_less']]);
                    $usedLess[] = $customField['product_view_custom_less'];
                } */
                if (!empty($customField['category_view_custom_less'])) {
                    //$usedLess = array_merge($usedLess, [$customField['category_view_custom_less']]);
                    $usedLess[] = $customField['category_view_custom_less'];
                }
            }
            if (!empty($customField['auto_detect_files'])) {
                $usedLess = array_merge($usedLess, $customField['auto_detect_files']);
            }
            if (!empty($customField['required_less_component'])) {
                $usedLess = array_merge($usedLess, $customField['required_less_component']);
            }
            
            $usedLess = array_unique($usedLess);
            if (count($usedLess)) {
                $flexibleFileList = $this->getFlexibleFileList();
                foreach ($usedLess as $flexibleLess) {
                    //if (in_array($flexibleLess, $flexibleFileList)) {
                        $content .= "@import (optional,less)'../general/flexible/" . $flexibleLess .  "';\n";
                    //}
                }
            }
        }
        if ($this->getData('parent')) {
            $parentModel = $this;
            $parent = $parentModel->getData('parent');
            $import = [];
            while ($parent) {
                if ($parent != $this->getData('identifier')) {
                    $import[] = "@import (less)'../{$parent}/" . $this->_elementsFileName . "';\n";
                    $parentModel = $this->_objectManager->create(get_class($this))->load($parent, 'identifier');
                    $parent = $parentModel->getData('parent');
                } else {
                    $parent = null;
                }
            }
            for ($i = count($import); $i > 0; $i--) {
                $content .= $import[$i - 1];
            }
        }
        $content .= "@import (less)'" . $this->_elementsFileName . "';";
        return $content;
    }
    
    public function save()
    {
        $initData = $this->getInitialData();
        if (!empty($initData['variables'])) {
            $defaultVariables = (array)json_decode($initData['variables'], true);
            $variables = (array)json_decode($this->getData('variables'), true);
            $variables = array_replace($defaultVariables, $variables);
            $this->setData('variables', json_encode($variables));
        }
        if ($customFields = $this->getData('custom_fields')) {
            if (!is_array($customFields)) {
                $customFields = (array)json_decode($customFields, true);
            }
        } else {
            $customFields = [];
        }
        if (!empty($initData['custom_fields'])) {
            $defaultCustomFields = (array)json_decode($initData['custom_fields'], true);
            $customFields = array_replace($defaultCustomFields, $customFields);
        }
        $customFields['version'] = uniqid();
        if (!empty($customFields['product_view_style'])) {
            $file = $customFields['product_view_style'];
            $fileNum = explode('catalog_product_view_style', $file);
            $fileNum = $fileNum[1];
            $customFields['product_view_less'] = "_product-view-style-{$fileNum}.less.css";
        }
        if (!empty($customFields['product_view_custom_less'])) {
            $customFields['product_view_custom_less'] = trim($customFields['product_view_custom_less']);
            if ($customFields['product_view_custom_less']) {
                $customFields['product_view_style'] = $customFields['product_view_custom_less'];
            }
        }
        
        if (!empty($customFields['category_view_style'])) {
            $file = $customFields['category_view_style'];
            $fileNum = explode('.phtml', $file);
            $fileNum = explode('product/list/list-styles/list-style', $fileNum[0]);
            $fileNum = $fileNum[1];
            $customFields['category_view_less'] = "product-{$fileNum}.less.css";
        }
        $this->setData('custom_fields', json_encode($customFields));
        $this->autoImportLessFiles();
        return parent::save();
    }
    
    protected function _getMainHtml()
    {
        return $this->getData('themelayout_content');
    }
    
    public function autoImportLessFiles()
    {
        if ($this->_autoImportLessFiles === null) {
            $this->_autoImportLessFiles = true;
            $mappingFile = $this->_projectDir . 'styles_mapping.xml';
            if ($this->io->fileExists($mappingFile, false)) {
                $customFields = (array)json_decode($this->getData('custom_fields'), true);
                $autoImport = isset($customFields['auto_import_less_files']) ? (int)$customFields['auto_import_less_files'] : 1;
                if ($autoImport) {
                    $xmlParser = $this->_objectManager->create(\Magento\Framework\Xml\Parser::class);
                    $xmlParser->load($mappingFile);
                    $data = $xmlParser->xmlToArray();
                    $styles = $data['styles'];
                    $content = $this->_getMainHtml();
                    $importFiles = [];
                    foreach ($styles as $class => $file) {
                        if (stripos((string)$content, $class) !== false) {
                            $files = explode(',', $file);
                            foreach ($files as $f) {
                                $importFiles[$f] = $f;
                            }
                        }
                    }
                    if (count($importFiles)) {
                        $customFields['auto_detect_files'] = [];
                        $customFields['flexible_less'] = isset($customFields['flexible_less']) ? (array)$customFields['flexible_less'] : [];
                        foreach ($importFiles as $file) {
                            if (!in_array($file, $customFields['flexible_less'])) {
                                $customFields['auto_detect_files'][] = $file;
                            }
                        }
                        $this->setData('custom_fields', json_encode($customFields));
                    }
                }
            }
        }
    }
    
    public function getInitialData()
    {
        if ($this->initData === false) {
            $elDefaultFile = $this->_projectDir . 'default.xml';
            if ($this->io->fileExists($elDefaultFile, false)) {
                $xmlParser = $this->_objectManager->create('Magento\Framework\Xml\Parser');
                $xmlParser->load($elDefaultFile);
                $data = $xmlParser->xmlToArray();
                $data = $data['config'];
                unset($data['identifier']);
                unset($data['title']);
                $data['variables'] = json_encode($data['variables']);
                if (isset($data['custom_fields'])) {
                    foreach ($data['custom_fields'] as $name => $field) {
                        if (isset($data['custom_fields'][$name]['item'])) {
                            $data['custom_fields'][$name] = $data['custom_fields'][$name]['item'];
                        }
                    }
                    $data['custom_fields'] = json_encode($data['custom_fields']);
                }
                $this->initData = $data;
            } else {
                $this->initData = [];
            }
        }
        return $this->initData;
    }
    
    protected function _getVarFileContent()
    {
        $variables = json_decode($this->getData('variables'), true);
        $initData = $this->getInitialData();
        if (!empty($initData['variables'])) {
            $defaultVariables = (array)json_decode($initData['variables'], true);
            $variables = array_replace($defaultVariables, $variables);
        }
        if (!$variables) {
            $variables = [];
        }
        ksort($variables);
        $content = '';
        foreach ($variables as $varName => $varValue) {
            $content .= $this->_assignLessVar($varName, $varValue);
        }
        $customField = json_decode($this->getData('custom_fields'), true);
        if (!empty($customField['custom_variables'])) {
            $content .= $customField['custom_variables'];
        }
        
        return $content;
    }
    
    protected function _assignLessVar($varName, $varValue)
    {
        $varValue = trim((string)$varValue);
        if (strpos($varValue, ' ') !== false) {
            $varValue = "~'{$varValue}'";
        }
        if (!$varValue) {
			if (strpos($varName, 'background_file')!== false) {
				$varValue = "''";
			} else {
				$varValue = "transparent";
			}
        }
        if ($varValue == "''" && (strpos($varName, 'background_file')!== false)) {
            $varValue = $this->_tsprImg;
        } elseif (strpos($varName, 'background_file')!== false) {
            $varValue = "~'" .$this->_imagesPath . "{$varValue}'";
        }
        return "@{$varName}:{$varValue};";
    }
    
    public function mediaFileExists($file, $isFile = true) {
        return $this->io->fileExists($this->_mediaPath . $file, $isFile);
    }
    
    public function getMediaUrl($path) {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$path;
    }
    
	public function buildFlixibleCss($fileName)
	{
		
	}
    
    public function delete()
    {   
        $children = $this->_objectManager->get(get_class($this))
            ->getCollection()
            ->addFieldToFilter('parent', $this->getIdentifier());
        if ($children->count()) {
            $childIdentifier = array();
            foreach ($children as $child) {
                $childIdentifier[] = '"'.$child->getIdentifier().'"';
            }
            throw new \Exception(
                __('Cannot delete %1 because it is parent of %2. Please unassigned "Extends CSS from" value for its children first.', '"'.$this->getIdentifier().'"', implode(', ', $childIdentifier))
            );
        }
        
        $elDirName = $this->_getElementDirName();
        $elDir = $this->_projectDir . $elDirName . '/';
        $this->io->rmdir($elDir, true);
        return parent::delete();
    }
	
}