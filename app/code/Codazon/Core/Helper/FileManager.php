<?php
/**
* Copyright © 2020 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Core\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileManager extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $filesystem;
    
    protected $io;
    
    protected $objectManager;
    
    protected $dirHander;
    
    protected $mediaBaseDir;
    
    protected $moduleReader;
    
    protected $sampleContext;
    
    protected $fixtureManager;
    
    protected $csvReader;
    
    protected $xmlParser;
    
    protected $xmlGenerator;
    
    protected $stylesHelper;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Setup\SampleData\Context $sampleContext,
        \Magento\Framework\Xml\Parser $xmlParser,
        \Magento\Framework\Xml\Generator $xmlGenerator
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->dirHander = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->mediaBaseDir = $this->dirHander->getAbsolutePath();
        $this->io = $io;
        $this->moduleReader = $moduleReader;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->sampleContext = $sampleContext;
        $this->fixtureManager = $this->sampleContext->getFixtureManager();
        $this->csvReader = $this->sampleContext->getCsvReader();
        $this->xmlParser = $xmlParser;
        $this->xmlGenerator = $xmlGenerator;
    }
    
    public function getObjectManager()
    {
        return $this->objectManager;
    }
    
    public function getStylesHelper()
    {
        if (null === $this->stylesHelper) {
            $this->stylesHelper = $this->objectManager->get(\Codazon\Core\Helper\Styles::class);
        }
        return $this->stylesHelper;
    }
    
    public function getSampleContext()
    {
        return $this->sampleContext;
    }
    
    public function getCsvReader()
    {
        return $this->csvReader;
    }
    
    public function getFixtureManager()
    {
        return $this->fixtureManager;
    }
    
    public function getIo()
    {
        return $this->io;
    }
    
    public function fileExists($file) {
        return $this->io->fileExists($file);
    }
    
    public function write($file, $content, $mode = 0666)
    {
        $this->io->write($file, $content, $mode);
    }
    
    public function read($file)
    {
        return $this->io->read($file);
    }
    
    public function getXmlParser()
    {
        return $this->xmlParser;
    }
    
    public function getXmlGenerator()
    {
        return $this->xmlGenerator;
    }
    
    public function getEtcXmlFilePath($fileName, $moduleName)
    {
        return $this->moduleReader->getModuleDir('etc', $moduleName) . '/' . $fileName;
    }
    
    public function getArrayFromXmlFile($filePath)
    {
        return $this->xmlParser->load($filePath)->xmlToArray();
    }
    
    public function getXmlFromArray($array)
    {
        return $this->xmlGenerator->arrayToXml($array);
    }
    
    public function getModuleReader()
    {
        return $this->moduleReader;
    }
}