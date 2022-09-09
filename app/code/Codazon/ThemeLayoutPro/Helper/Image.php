<?php
/**
 * Copyright © Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Blog image helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Image extends AbstractHelper
{
    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     */
    protected $_quality = 90;
    protected $_keepAspectRatio = true;
    protected $_keepFrame = true;
    protected $_keepTransparency = true;
    protected $_constrainOnly = true;
    protected $_backgroundColor = [255, 255, 255];
    protected $_baseFile;
    protected $_newFile;
    protected $_baseDir = 'codazon_cache/slideshow';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_imageFactory = $imageFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function init($baseFile)
    {
        $this->_newFile = '';
        $this->_baseFile = $baseFile;
        $this->_baseDir = 'slideshow/cache/';
        $this->_keepFrame = true;
        return $this;
    }
    
    public function setBaseDir($baseDir)
    {
        $this->_baseDir = $baseDir;
        return $this;
    }
    
    public function keepFrame($keepFrame)
    {
        $this->_keepFrame = $keepFrame;
        return $this;
    }

    public function resize($width, $height = null)
    {
        if ($this->_baseFile){
            $path = $this->_baseDir . $width . 'x' . $height;
            $this->_newFile = $path. '/' . $this->_baseFile;
            if (!$this->fileExists($this->_newFile)) {
                $this->resizeBaseFile($width, $height);
            }
        }
        return $this;
    }

    protected function resizeBaseFile($width, $height)
    {
        if (!$this->fileExists($this->_baseFile)) {
            $this->_baseFile = null;
            return $this;
        }

        $processor = $this->_imageFactory->create(
            $this->_mediaDirectory->getAbsolutePath($this->_baseFile)
        );
        $processor->keepAspectRatio($this->_keepAspectRatio);
        $processor->keepFrame($this->_keepFrame);
        $processor->keepTransparency($this->_keepTransparency);
        $processor->constrainOnly($this->_constrainOnly);
        $processor->backgroundColor($this->_backgroundColor);
        $processor->quality($this->_quality);
        
        if ($height === null) {
            $height = $processor->getOriginalHeight()*$width/$processor->getOriginalWidth();
        }
        
        $processor->resize($width, $height);

        $newFile = $this->_mediaDirectory->getAbsolutePath($this->_newFile);
        $processor->save($newFile);
        unset($processor);

        return $this;
    }

    protected function fileExists($filename)
    {
        return $this->_mediaDirectory->isFile($filename);
    }

    public function __toString()
    {
        $url = "";
        if ($this->_baseFile){
            $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $this->_newFile;
        }
        return $url;
    }
}
