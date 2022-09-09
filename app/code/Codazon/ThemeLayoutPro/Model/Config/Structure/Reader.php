<?php
/**
 * Backend System Configuration reader.
 * Retrieves system configuration form layout from system.xml files. Merges configuration and caches it.
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Structure;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;

/**
 * Class Reader
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config' => 'id',
        '/config/system/section' => 'id',
        '/config/system/section(/group)+' => 'id',
        '/config/system/section(/group)+/field' => 'id',
        '/config/system/section(/group)+/field/depends/field' => 'id',
        '/config/system/section(/group)+/field/options/option' => 'label',
    ];

    /**
     * @var CompilerInterface
     */
    protected $compiler;


    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Config\Model\Config\Structure\Converter $converter,
        \Magento\Config\Model\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        CompilerInterface $compiler,
        \Magento\Framework\Module\Dir\Reader $dirReader,
        $fileName = 'system.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        $this->dirReader = $dirReader;
        $this->compiler = $compiler;
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
        $this->_schemaFile = $this->dirReader->getModuleDir('etc','Codazon_ThemeLayoutPro') . '/variables.xsd';
        $this->_perFileSchema = $this->dirReader->getModuleDir('etc','Codazon_ThemeLayoutPro') . '/variables_file.xsd';
    }

    /**
     * Read configuration files
     *
     * @param array $fileList
     * @return array
     * @throws LocalizedException
     */
    
    public function read($scope = null)
    {
        $scope = $scope ?: $this->_defaultScope;
        $fileList = $this->_fileResolver->get($this->_fileName, $scope);
        if (!count($fileList)) {
            return [];
        }
        $output = $this->_readFiles($fileList);

        return $output;
    }
    
    protected function _readFiles($fileList)
    {

        /** @var \Magento\Framework\Config\Dom $configMerger */
        $configMerger = null;
        foreach ($fileList as $key => $content) {
            try {
                $content = $this->processingDocument($content);
                if (!$configMerger) {
                    $configMerger = @$this->_createConfigMerger($this->_domDocumentClass, $content);
                } else {
                    $configMerger->merge($content);
                }
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase("Invalid XML in file %1:\n%2", [$key, $e->getMessage()])
                );
            }
        }

        if ($this->validationState->isValidationRequired()) {
            $errors = [];
            if ($configMerger && !$configMerger->validate($this->_schemaFile, $errors)) {
                $message = "Invalid Document \n";
                throw new LocalizedException(
                    new \Magento\Framework\Phrase($message . implode("\n", $errors))
                );
            }
        }

        $output = [];
        if ($configMerger) {
            $output = $this->_converter->convert($configMerger->getDom());
        }

        return $output;
    }

    /**
     * Processing nodes of the document before merging
     *
     * @param string $content
     * @return string
     */
    protected function processingDocument($content)
    {
        $object = new DataObject();
        $document = new \DOMDocument();

        $document->loadXML($content);
        $this->compiler->compile($document->documentElement, $object, $object);

        return $document->saveXML();
    }
}
