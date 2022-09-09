<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\App\Config\Initial;

use Magento\Framework\App\Filesystem\DirectoryList;

class Reader
{
    /**
     * File locator
     *
     * @var \Magento\Framework\Config\FileResolverInterface
     */
    protected $_fileResolver;

    /**
     * Config converter
     *
     * @var  \Magento\Framework\Config\ConverterInterface
     */
    protected $_converter;

    /**
     * Config file name
     *
     * @var string
     */
    protected $_fileName;

    /**
     * Class of dom configuration document used for merge
     *
     * @var string
     */
    protected $_domDocumentClass;

    /**
     * Scope priority loading scheme
     *
     * @var array
     */
    protected $_scopePriorityScheme = ['global'];

    /**
     * Path to corresponding XSD file with validation rules for config
     *
     * @var string
     */
    protected $_schemaFile;

    protected $_themeFactory;
    
    protected $_theme = false;
    
    protected $_io;
    
    protected $_localeFile;
    
    protected $_coreRegistry = null;
    
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Config\ConverterInterface $converter
     * @param SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\DomFactory $domFactory
     * @param string $fileName
     * @param string $domDocumentClass
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\App\Config\Initial\Converter\Interceptor $converter,
        \Codazon\ThemeLayoutPro\App\Config\Initial\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\DomFactory $domFactory,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Framework\View\Design\FileResolution\Fallback\LocaleFile $localeFile,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\Registry $registry,
        $fileName = 'theme_config.xml'
    ) {
        $this->_schemaFile = $schemaLocator->getSchema();
        $this->_fileResolver = $fileResolver;
        $this->_converter = $converter;
        $this->domFactory = $domFactory;
        $this->_fileName = $fileName;
        $this->_themeFactory = $themeFactory;
        $this->_localeFile = $localeFile;
        $this->_io = $io;
        $this->_coreRegistry = $registry;
    }

 
    protected function getThemeConfigFile()
    {
        if ($currentTheme = $this->_coreRegistry->registry('current_theme')) {
            return $this->_localeFile->getFile('frontend', $currentTheme, null, 'etc/theme_config.xml');
        } else {
            
        }
    }
    
    /**
     * Read configuration scope
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function read()
    {
        /* $this->_fileName is 'theme_config.xml' */
        $fileList = [];
        
        
        /* default config */
        foreach ($this->_scopePriorityScheme as $scope) {
            $directories = $this->_fileResolver->get($this->_fileName, $scope);
            foreach ($directories as $key => $directory) {
                $fileList[$key] = $directory;
            }
        }
        
        /* Override by current theme config */
        
        if ($themeConfigFile = $this->getThemeConfigFile()) {
            $fileList[$themeConfigFile] = $this->_io->read($themeConfigFile);
        }
        
        if (!count($fileList)) {
            return [];
        }

        /** @var \Magento\Framework\Config\Dom $domDocument */
        $domDocument = null;
        foreach ($fileList as $file) {
            try {
                if (!$domDocument) {
                    $domDocument = $this->domFactory->createDom(['xml' => $file, 'schemaFile' => $this->_schemaFile]);
                } else {
                    $domDocument->merge($file);
                }
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase("Invalid XML in file %1:\n%2", [$file, $e->getMessage()])
                );
            }
        }

        $output = [];
        if ($domDocument) {
            $output = $this->_converter->convert($domDocument->getDom());
        }
        return $output;
    }
}
