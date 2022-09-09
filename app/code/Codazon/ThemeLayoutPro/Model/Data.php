<?php
/**
 *
 * Copyright © 2020 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class Data extends \Magento\Framework\Model\AbstractModel
{
    protected $helper;
    
    protected $mainContentModel;
    
    protected $headerModel;
    
    protected $footerModel;
    
    protected $storeManager;
    
    protected $fixtureManager;
    
    protected $csvReader;
    
    protected $version;
    
    protected $cmsBlockModel;
    
    protected $objectManager;
    
    protected $io;
    
    protected $fileSystem;
    
    protected $directoryList;
    
    protected $mageFileSystem;
    
    protected $magentoVersion;
    
    protected $packageName = 'infinit-magento2';
    
    protected $packageTitle = 'Codazon - Infinit - Magento 2.x';
    
    protected $docUrl = 'http://codazon.com/document/infinit/magento2';
    
    protected $quoteMask = '~~~';
    
    protected $_noExport = [
        'main_content' => [
            
        ],
        'header' => [
            
        ],
        'footer' => [
            
        ]
    ];
    
    protected function removeUnreleasedModules($fullTemPath = false)
    {
        if ($fullTemPath) {
            //$this->fileSystem->remove($this->getTempFullPath('app/code/Codazon/Core'));
            //$this->fileSystem->remove($this->getTempFullPath('app/code/Codazon/GoogleAmpManager'));
            $this->fileSystem->remove($this->getTempFullPath('app/code/Amasty'));
            $this->fileSystem->remove($this->getTempFullPath('pub/media/amasty'));
        } else {
            //$this->fileSystem->remove($this->getTempThemePath('app/code/Codazon/Core'));
            //$this->fileSystem->remove($this->getTempThemePath('app/code/Codazon/GoogleAmpManager'));
            $this->fileSystem->remove($this->getTempThemePath('app/code/Amasty'));
            $this->fileSystem->remove($this->getTempThemePath('pub/media/amasty'));
        }
        return $this;
    }
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Codazon\ThemeLayoutPro\Model\Header $headerModel,
        \Codazon\ThemeLayoutPro\Model\Footer $footerModel,
        \Codazon\ThemeLayoutPro\Model\MainContent $mainContentModel,
        \Magento\Cms\Model\Block $cmsBlockModel,
        \Codazon\ThemeLayoutPro\Helper\Data $helper,
        SampleDataContext $sampleDataContext,
        \Magento\Framework\Filesystem\Io\File $io
    ) {
        $this->storeManager = $storeManager;
        $this->headerModel = $headerModel;
        $this->footerModel = $footerModel;
        $this->mainContentModel = $mainContentModel;
        $this->cmsBlockModel = $cmsBlockModel;
        $this->helper = $helper;
        $this->storeId = $this->storeManager->getStore()->getId();
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->io = $io;
        $this->mageFileSystem = $this->objectManager->get('Magento\Framework\Filesystem');
        $this->directoryList = $this->objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        
        $this->fileSystem = new Filesystem();
        $this->finder = new Finder();
        $this->version = '0.1.0';
        $this->rootDir = $this->directoryList->getRoot();
    }
    
    public function setVersion($version)
    {
        $this->version = $version;
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    public function getTempFullPath($path = '')
    {
        return $this->getBuildPath('full/' . $path);
    }
    
    public function getTempImagesPath($path = '')
    {
        return $this->getBuildPath('images/' . $path);
    }
    
    public function getTempThemePath($path = '')
    {
        return $this->getBuildPath('theme/' . $path);
    }
    
    public function getPatchPath($path = '')
    {
        return $this->getBuildPath('patch/' . $path);
    }
    
    public function getPatchList($file)
    {
        $patchListFile = $this->getPatchPath('patch_list/' . $file);
        $list = include($patchListFile);
        if (is_array($list)) {
            if (isset($list['files'])) {
                foreach ($list['files'] as $i => $value) {
                    $list['files'][$i] = str_replace("\\", DIRECTORY_SEPARATOR, $value);
                }
            }
            if (isset($list['directories'])) {
                foreach ($list['directories'] as $i => $value) {
                    $list['directories'][$i] = str_replace("\\", DIRECTORY_SEPARATOR, $value);
                }
            }
        }
        return $list;
    }
    
    
    public function getBuildPath($path = '')
    {
        return $this->getAbsolutePath('build/' . $path);
    }
    
    public function getAbsolutePath($path = '')
    {
        return $this->rootDir . '/' . $path;
    }
    
    public function removeAll()
    {
        $this->fileSystem->remove($this->getTempFullPath());
        $this->fileSystem->remove($this->getTempThemePath());
        $this->fileSystem->remove($this->getTempImagesPath());
    }
    
    
    public function exportPatch($patchList, $zipFileName = 'patch-01.zip', $version = false)
    {
        $list = $this->getPatchList($patchList);
        if (is_array($list)) {
            $zipFile = $this->getPatchPath($zipFileName);
            $this->fileSystem->remove($zipFile);
            $zip = new ZipArchive();
            $source = $this->getTempImagesPath();
            $zip->open($zipFile, ZipArchive::CREATE);
            
            $this->finder = new Finder();
            if ($version) {
                $path = "{$this->packageName}-theme-package-release-note.txt";
                $content = "{$this->packageTitle} - Theme Package - version {$version}\r\nCopyright © 2020 Codazon\r\nDocumentation: {$this->docUrl}\r\nChangelog: {$this->docUrl}#changelog";        
                $releaseFile = $this->getAbsolutePath($path);
                $this->io->write($releaseFile, $content, 0666);
                if (!isset($list['files'])) {
                    $list['files'] = [];
                }
                $list['files'][] = $path;
                
                $path = "app/code/Codazon/ThemeLayoutPro/etc/version.txt";
                $versionFile = $this->getAbsolutePath($path);
                $currentVersion = $this->io->read($versionFile);
                $this->io->write($versionFile, $version, 0666);
                $list['files'][] = $path;
            }

            if (!empty($list['files'])) {
                $fileList = $this->finder->files()->in($this->getAbsolutePath());
                foreach ($list['files'] as $file) {
                    $fileList->path($file);
                }
                foreach ($fileList as $file) {
                    $zip->addFile($file->getRealpath(), $file->getRelativePathName());
                }
            }
            if (isset($list['directories'])) {
                foreach ($list['directories'] as $dir) {
                    $this->finder = new Finder();
                    $fileList = $this->finder->files();
                    $fileList->in($this->getAbsolutePath($dir));
                    foreach ($fileList as $file) {
                        $zip->addFile($file->getRealpath(), $dir . DIRECTORY_SEPARATOR . $file->getRelativePathName());
                    }
                }
            }
            
            $zip->close();
            if (isset($releaseFile)) {
                $this->fileSystem->remove($releaseFile);
            }
            if (isset($versionFile) && isset($currentVersion)) {
                $this->io->write($versionFile, $currentVersion, 0666);
            }
            
            echo "<xmp>";
            if (!empty($list['files'])) {
                echo "<ul>\n";
                foreach ($list['files'] as $file) {
                    echo "    <li>$file</li>\n";
                }
                echo "</ul>\n";
            }
            if (!empty($list['directories'])) {
                echo "<ul>\n";
                foreach ($list['directories'] as $directory) {
                    echo "    <li>$directory</li>\n";
                }
                echo "</ul>\n";
            }
            echo "</xmp>";
            $downloadLink = $this->helper->getScopeConfig('web/unsecure/base_url') . 'build/patch/' . $zipFileName;
            echo "<p style='color: green'>Completed!</p><p>Download Link: <a href='$downloadLink'>$downloadLink</a></p><p>Package Path: $zipFile</p>";
            echo "<p>File Size: ".round((filesize ($zipFile)/(1000*1000)), 2)." MB</p>";
        }
    }
    
    public function packProductImages()
    {
        echo "<h1 class='titlte'>Export Product Image</h1>";
        echo "<p>Version: {$this->version}</p>";
        $this->removeAll();
        $path = 'pub/media/catalog/product';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempImagesPath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()
            ->in($source)
            ->exclude([
                'cache'
            ]);
        $this->fileSystem->mirror($source, $dest, $fileList);
        $this->fileSystem->remove($this->getTempFullPath('app/code/local/Codazon/Flexibletheme/Model/Export.php'));
         /* Zip File */
        echo "<p style='font-weight:bold;color:blue'>Zip File</p>";
        $zipFileName = $this->packageName . '-v' . $this->version . '-product-images.zip';
        $zipFile = $this->getBuildPath($zipFileName);
        $this->fileSystem->remove($zipFile);
        try {
            $zip = new ZipArchive();
            $source = $this->getTempImagesPath();
            $zip->open($zipFile, ZipArchive::CREATE);
            $this->finder = new Finder();
            $fileList = $this->finder
                ->files()
                ->in($source);
            foreach ($fileList as $file) {
                $zip->addFile($file->getRealpath(), $file->getRelativePathName());
            }
            $zip->close();
        } catch (\Exception $e) {
            echo "<p style='font-weight:bold;color:blue'>Step 4. Error: " . $e->getMessage() . "</p>";
        }
        $downloadLink = $this->helper->getScopeConfig('web/unsecure/base_url') . 'build/' . $zipFileName;
        echo "<p style='color: green'>Completed!</p><p>Download Link: <a href='$downloadLink'>$downloadLink</a></p><p>Package Path: $zipFile</p>";
        echo "<p>File Size: ".round((filesize ($zipFile)/(1000*1000)), 2)." MB</p>";
        $this->removeAll();
    }
    
    public function packFull()
    {
        /* Export Database */
        $this->removeAll();
        if (!$this->getData('skip_export_database')) {
            $dbDir = $this->getAbsolutePath('db');
            try{
                $params = $this->mainContentModel->getCollection()->getConnection()->getConfig();    
                $command1 = "mysqldump -u {$params['username']} --password={$params['password']} -d --skip-triggers --order-by-primary --comments=FALSE {$params['dbname']} > {$this->rootDir}/db/1_schema.sql;";
                $command2 = "mysqldump -u {$params['username']} --password={$params['password']} -t --skip-triggers --order-by-primary --comments=FALSE {$params['dbname']} > {$this->rootDir}/db/2_init_data.sql;";
                echo "<p>Run Export Database Command:</p>";
                exec($command1);
                exec($command2);
                
                $schemaFile = "{$this->rootDir}/db/1_schema.sql";
                $content = $this->io->read($schemaFile);
                $content = str_replace("DEFINER=`{$params['username']}`@`localhost`", "DEFINER=CURRENT_USER", $content);
                $this->io->write($schemaFile, $content, 0666);
                
            } catch (\Exception $e) {
                echo "<p>Error: " . $e->getMessage() . "</p>";
            }
        }
        
        /* Move Directories */
        echo "<p>Move Directories</p>";
        $this->fileSystem->remove($this->getTempFullPath());
        $path = '';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempFullPath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()->ignoreDotFiles(false)
            ->in($source)
            ->notName('*.bk')
            ->notName('*.bk.sql')
            ->notName('export_db.sh')
            ->notName('setup_db.sh')
            ->notName('intropage.html')
            ->notName('._.DS_Store')
            ->notName('.DS_Store')
            ->notName('main-styles.less.min.css')
            ->notName('*.config')
            ->exclude(['.git', '.github', 'pub/media/catalog/product', 'pub/media/catalog/tmp', 'pub/media/codazon_cache',
                'pub/media/slideshow/cache', 'pub/media/blog/cache', 'pub/media/tmp/catalog', 'pub/static/adminhtml', 'pub/static/frontend',
                'pub/media/import', 'pub/media/wysiwyg/codazon/Blog', 'build/full', 'build/patch'
            ]);
        $this->fileSystem->mirror($source, $dest, $fileList);
        $this->fileSystem->remove($this->getTempFullPath('db/bk'));
        $this->fileSystem->remove($this->getTempFullPath('node_modules'));
        $this->fileSystem->remove($this->getTempFullPath('build'));
        $this->fileSystem->remove($this->getTempFullPath('themesetup'));
        $this->fileSystem->remove($this->getTempFullPath('generated/code'));
        $this->fileSystem->remove($this->getTempFullPath('generated/metadata'));
        $this->fileSystem->remove($this->getTempFullPath('intro'));
        $this->fileSystem->remove($this->getTempFullPath('var'));
        $this->fileSystem->remove($this->getTempFullPath('bk'));
        $this->fileSystem->remove($this->getTempFullPath('index.php'));
        $this->fileSystem->remove($this->getTempFullPath('app/etc/config.php'));
        $this->fileSystem->remove($this->getTempFullPath('app/etc/env.php'));
        $this->fileSystem->remove($this->getTempFullPath('pub/media/wysiwyg/codazon/Blog'));
        $this->fileSystem->remove($this->getTempFullPath('pub/media/wysiwyg/.thumbs'));
        $this->fileSystem->remove($this->getTempFullPath('pub/media/.thumbscatalog'));
        $this->fileSystem->remove($this->getTempFullPath('pub/media/.thumbsmagefan_blog'));
        $this->fileSystem->remove($this->getTempFullPath('pub/media/.thumbssmall_logo'));
        $this->fileSystem->remove($this->getTempFullPath('pub/media/.thumbswysiwyg'));
        //$this->fileSystem->remove($this->getTempFullPath('pub/media/wysiwyg/codazon/Brand'));
        $this->fileSystem->remove($this->getTempFullPath('app/code/Codazon/ThemeLayoutPro/Controller/Export/Index.php'));
        $this->fileSystem->remove($this->getTempFullPath('app/code/Codazon/GoogleAmpManager/Controller/Amphandle/Data/Export.php'));
        $this->removeUnreleasedModules(true);
        
        $path = 'var/vendor';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempFullPath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()->ignoreDotFiles(false)
            ->in($source)->notName('._.DS_Store')
            ->notName('.DS_Store');
        $this->fileSystem->mirror($source, $dest, $fileList);
        
        /* Release Note */
        $path = "{$this->packageName}-full-package-release-note.txt";
        
        $content = "{$this->packageTitle} - Full Package - version {$this->version}\r\nCopyright © 2020 Codazon\r\nDocumentation: {$this->docUrl}\r\nChangelog: {$this->docUrl}#changelog";        
        $file = $this->getTempFullPath($path);
        $this->io->write($file, $content, 0666);
        echo "<p>Release Note:</p><pre style='border: 1px solid #000; padding: 10px 10px'>$content</pre>";
        
        $path = "app/code/Codazon/ThemeLayoutPro/etc/version.txt";
        $versionFile = $this->getTempFullPath($path);
        $this->io->write($versionFile, $this->version, 0666);
        
        /* AMP module */
        $path = 'pub/media/codazon/amp';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempFullPath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()->in($source);
        $this->fileSystem->mirror($source, $dest, $fileList);
        $this->io->rmdirRecursive($this->getTempFullPath('pub/media/codazon/amp/less/destination'));
        $this->io->mkdir($this->getTempFullPath('pub/media/codazon/amp/less/destination'));
        $this->io->write($this->getTempFullPath('pub/media/codazon/amp/less/destination/placeholder.txt'), '', 0666);
        $this->io->mkdir($this->getTempFullPath('pub/media/codazon/amp/less/scope'));
        $this->io->write($this->getTempFullPath('pub/media/codazon/amp/less/scope/placeholder.txt'), '', 0666);
        
        /* Zip File */
        $zipFileName = $this->packageName . '-v' . $this->version . '-fullpackage.zip';
        $zipFile = $this->getBuildPath($zipFileName);
        $this->fileSystem->remove($zipFile);
        
        $zip = new ZipArchive();
        $source = $this->getTempFullPath();
        $zip->open($zipFile, ZipArchive::CREATE);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()->ignoreDotFiles(false)
            ->in($source);
        foreach ($fileList as $file) {
            $zip->addFile($file->getRealpath(), $file->getRelativePathName());
        }
        $zip->close();
        
        $downloadLink = $this->helper->getScopeConfig('web/unsecure/base_url') . 'build/' . $zipFileName;
        echo "<p style='color: green'>Completed!</p><p>Download Link: <a href='$downloadLink'>$downloadLink</a></p><p>Package Path: $zipFile</p>";
        echo "<p>File Size: ".round((filesize ($zipFile)/(1000*1000)), 2)." MB</p>";
        $this->removeAll();
    }

    public function packTheme()
    {
        //$this->fileSystem->remove($this->getTempThemePath());
        $this->removeAll();
        /* Code */
        $path = 'app/code';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempThemePath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()
            ->in($source);
        $this->fileSystem->mirror($source, $dest, $fileList);
        //$this->fileSystem->remove($this->getTempThemePath('app/code/Codazon/ThemeLayoutPro/Model/Data.php'));
        $this->fileSystem->remove($this->getTempThemePath('app/code/Codazon/ThemeLayoutPro/Controller/Export/Index.php'));
        $this->fileSystem->remove($this->getTempThemePath('app/code/Codazon/GoogleAmpManager/Controller/Amphandle/Data/Export.php'));
        
        /* Design */
        $path = 'app/design';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempThemePath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()
            ->in($source);
        $this->fileSystem->mirror($source, $dest, $fileList);
        
        /* Media */
        $path = 'pub/media';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempThemePath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()
            ->in($source)
            ->notName('*bk')
            ->notName('*.css.map')
            ->notName('main-styles.less.min.css')
            ->notName('header-styles.less')
            ->notName('main-styles.less')
            ->notName('footer-styles.less')
            ->notName('*.config')
            ->exclude(['attribute','blog', 'captcha', 'catalog', 'codazon_cache',  'customer',
                'downloadable', 'import', 'logo', 'small_logo', 'theme_customization', 'tmp',
                'codazon/lookbook/category', 'codazon/lookbook/item', 'codazon/lookbook/item_element', 'codazon/lookbook/tmp',
                'bk'
            ]);
        $this->fileSystem->mirror($source, $dest, $fileList);
        $this->fileSystem->remove($this->getTempThemePath('pub/media/slideshow'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/wysiwyg/codazon/Blog'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/wysiwyg/codazon/Brand'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/wysiwyg/codazon/marketplace'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/wysiwyg/.thumbs'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/.thumbscatalog'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/.thumbsmagefan_blog'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/.thumbssmall_logo'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/.thumbswysiwyg'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/custom_options'));
        $this->fileSystem->remove($this->getTempThemePath('pub/media/custom_options'));
        for ($fi = 1; $fi <= 40; $fi++) {
            $this->fileSystem->remove($this->getTempThemePath("pub/media/cdzinsta/{$fi}"));
        }
        
        /* AMP module */
        $path = 'pub/media/codazon/amp';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempThemePath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()->in($source);
        $this->fileSystem->mirror($source, $dest, $fileList);
        $this->io->rmdirRecursive($this->getTempThemePath('pub/media/codazon/amp/less/destination'));
        $this->io->mkdir($this->getTempThemePath('pub/media/codazon/amp/less/destination'));
        $this->io->mkdir($this->getTempThemePath('pub/media/codazon/amp/less/scope'));
        $this->io->write($this->getTempThemePath('pub/media/codazon/amp/less/destination/placeholder.txt'), '', 0666);
        $this->io->write($this->getTempThemePath('pub/media/codazon/amp/less/scope/placeholder.txt'), '', 0666);
        
        /* Remove unreleased modules */
        $this->removeUnreleasedModules();
        
        
        /* Theme Setup */
        /* $path = 'themesetup';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempThemePath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()->ignoreDotFiles(false)
            ->in($source);
        $this->fileSystem->mirror($source, $dest, $fileList); */
        
        /* Lib */
        $path = 'lib/internal/Mageplaza';
        $source = $this->getAbsolutePath($path);
        $dest = $this->getTempThemePath($path);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()->ignoreDotFiles(false)
            ->in($source);
        $this->fileSystem->mirror($source, $dest, $fileList);
        
        /* Release Note */
        $path = "{$this->packageName}-theme-package-release-note.txt";
        
        $content = "{$this->packageTitle} - Theme Package - version {$this->version}\r\nCopyright © 2020 Codazon\r\nDocumentation: {$this->docUrl}\r\nChangelog: {$this->docUrl}#changelog";
        
        $file = $this->getTempThemePath($path);
        $this->io->write($file, $content, 0666);
        
        /* Version File */
        $path = "app/code/Codazon/ThemeLayoutPro/etc/version.txt";
        $versionFile = $this->getTempThemePath($path);
        $this->io->write($versionFile, $this->version, 0666);
        
        /* Zip File */
        $zipFile = $this->getBuildPath($this->packageName . '-v' . $this->version . '-themepackage.zip');
        $this->fileSystem->remove($zipFile);
        
        $zip = new ZipArchive();
        $source = $this->getTempThemePath();
        $zip->open($zipFile, ZipArchive::CREATE);
        $this->finder = new Finder();
        $fileList = $this->finder
            ->files()->ignoreDotFiles(false)
            ->in($source);
        foreach ($fileList as $file) {
            $zip->addFile($file->getRealpath(), $file->getRelativePathName());
        }
        $zip->close();
        $this->removeAll();
    }
    
    public function exportMenus($file = null, $ids = [])
    {
        $collection = $this->objectManager->get('\Codazon\MegaMenu\Model\Megamenu')->getCollection()->setPageSize(1000);
        if (count($ids)) {
            $collection->addFieldToFilter('menu_id', $ids);
        }
        $result = [];
        $header = ['identifier', 'title', 'type', 'content', 'is_active', 'style'];
        $rows = [];
        $result['items'] = [];
        
        $rows[] = $header;
        foreach ($collection->getItems() as $item) {
            $itemData = [];
            foreach ($header as $column) {
                $itemData[$column] = $item->getData($column);
            }
            $rows[] = $this->encodeData($itemData);
            $result['items'][] = [
                'id'    => $item->getId(),
                'name'  => $item->getData('title')
            ];
        }
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_MegaMenu::fixtures/codazon_megamenu.csv');
        }
        $this->csvReader->saveData($file, $rows);
        return $result;
    }
    
    public function buildProjectAssets($productPath, $exportToDefault = true, $updateVersion = false)
    {
        $data = explode("/", $productPath);
        $type = $data[0];
        $identifier = empty($data[1]) ? false : $data[1];
        $result = ['success' => false, 'message' => 'Cannot deploy assets right now.'];
        if ($type == 'header') {
            $collection = $this->headerModel->getCollection();
        } elseif ($type == 'main') {
            $collection = $this->mainContentModel->getCollection()->setStoreId(0)->addAttributeToSelect(['themelayout_title', 'themelayout_content']);
        } elseif ($type == 'footer') {
            $collection = $this->footerModel->getCollection();
        }
        if ($identifier) {
            $collection->addFieldToFilter('identifier', $identifier);
        }
        if (isset($collection) && $collection->count()) {
            $title = [];
            try {
                foreach ($collection as $item) {
                    //$item = $collection->getFirstItem();
                    $item->setStoreId(0)->load($item->getId());
                    if ($updateVersion) {
                        $customFields = json_decode((string)$item->getData('custom_fields'), true);
                        $customFields['version'] = uniqid();
                        $item->setData('custom_fields', json_encode($customFields));
                        $item->save();
                    }
                    $item->updateWorkspace($exportToDefault);
                    $result['success']          = true;
                    $elementTitle    = $item->getData('title') ? : $item->getData('themelayout_title');
                    $title[] = '<title>' . $elementTitle . '</title>';
                    
                }
                $title = implode(', ', $title);
                $result['message']          = "Deploy assets for {$title} successfully.";
            } catch (\Exception $e) {
                $result['message']          = $e->getMessage();
            }                        
        } else {
             $result['message']             = 'Project(s) not found.'; 
        }
        return $result;
    }
    
    public function buildAssets($onlyMainContent = false, $exportToDefault = true, $updateVersion = false)
    {
        $result = [];
        
        $result['main_content'] = [];
        $collection = $this->mainContentModel->getCollection()->setStoreId(0)->setPageSize(1000)
            ->addAttributeToSelect(['themelayout_title', 'themelayout_content']);
        foreach ($collection->getItems() as $item) {
            try {
                if ($updateVersion) {
                    $customFields = json_decode((string)$item->getData('custom_fields'), true);
                    $customFields['version'] = uniqid();
                    $item->load($item->getId())->setData('custom_fields', json_encode($customFields));
                    $item->save();
                }
                $item->updateWorkspace($exportToDefault);
                $result['main_content'][] = $item->getData('themelayout_title');
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
        
        if (!$onlyMainContent) {
            $result['header'] = [];
            $collection = $this->headerModel->getCollection()->setPageSize(1000);
            foreach ($collection->getItems() as $item) {
                try {
                    $item->setStoreId(0)->load($item->getId());
                    if ($updateVersion) {
                        $customFields = json_decode((string)$item->getData('custom_fields'), true);
                        $customFields['version'] = uniqid();
                        $item->setData('custom_fields', json_encode($customFields));
                        $item->save();
                    }
                    $item->updateWorkspace($exportToDefault);
                    $result['header'][] = $item->getData('title');
                } catch (\Exception $e) {
                    die($e->getMessage());
                }
            }
            
            $result['footer'] = [];
            $collection = $this->footerModel->getCollection()->setPageSize(1000);
            foreach ($collection->getItems() as $item) {
                try {
                    if ($updateVersion) {
                        $customFields = json_decode((string)$item->getData('custom_fields'), true);
                        $customFields['version'] = uniqid();
                        $item->setStoreId(0)->load($item->getId())->setData('custom_fields', json_encode($customFields));
                        $item->save();
                    }
                    $item->updateWorkspace($exportToDefault);
                    $result['footer'][] = $item->getData('title');
                } catch (\Exception $e) {
                    die($e->getMessage());
                }
            }
        }
        return $result;
    }
    
    public function getMagentoVersion()
    {
        if ($this->magentoVersion === null) {
            $this->magentoVersion = $this->objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
        }
        return $this->magentoVersion;
        
    }
    
    protected function isMagento22x()
    {
        return version_compare($this->getMagentoVersion(), '2.2.0', '>=');
    }
    
    public function encodeData($itemData) {
        foreach ($itemData as $column => $item) {
            $itemData[$column] = str_replace('"',$this->quoteMask, (string)$itemData[$column]);
        }
        return $itemData;
    }
    
    public function exportMainContent($file = null, $ids = [])
    {
        $collection = $this->mainContentModel->getCollection()->setStoreId(0)->setPageSize(1000)
            ->addAttributeToSelect('*');
        if (count($ids)) {
            $collection->addFieldToFilter('entity_id', $ids);
        }
        $result = [];
        $result['items'] = [];
        
        $header = ['identifier', 'themelayout_title', 'is_active', 'variables', 'custom_variables', 'parent', 'custom_fields', 'themelayout_content', 'store_options'];
        $rows[] = $header;
        
        $serializeConditionHelper = $this->objectManager->get('\Codazon\ThemeLayoutPro\Helper\SerializedConditions');
        $jsonConditionHelper = $this->objectManager->get('\Codazon\ThemeLayoutPro\Helper\JsonConditions');
        
        foreach ($collection->getItems() as $item) {
            try {
                if (!empty($this->_noExport['main_content'])) {
                    if (in_array($item->getData('identifier'), $this->_noExport['main_content'])) {
                        continue;
                    }
                }
                $item->setData('title', $item->getData('themelayout_title'));
                $item->updateWorkspace(true);
                
                $itemData = [];
                foreach ($header as $column) {
                    $itemData[$column] = $item->getData($column);
                }
                
                if (!$this->isMagento22x()) {
                    $needReplace = [];
                    $pattern = '/conditions_encoded=([.\\\]+)"(.*?)([.\\\]+)\"/si';
                    if (preg_match_all($pattern, $itemData['themelayout_content'], $constructions, PREG_SET_ORDER)) {
                        foreach($constructions as $index => $construction) {
                            $needReplace[] = $construction[2];
                        }
                    }
                    $needReplace = array_unique($needReplace);
                    foreach ($needReplace as $replace) {
                        $condition = $jsonConditionHelper->encode($serializeConditionHelper->decode($replace));
                        $itemData['themelayout_content'] = str_replace($replace, $condition, $itemData['themelayout_content']);
                    }
                }
                $rows[] = $this->encodeData($itemData);
                $result['items'][] = [
                    'id'    => $item->getId(),
                    'name'  => $item->getData('themelayout_title')
                ];
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_maincontent_entity.csv');
        }
        $this->csvReader->saveData($file, $rows);
        
        return $result;
    }
    
    public function exportHeader($file = null, $ids = [])
    {
        $collection = $this->headerModel->getCollection()->setPageSize(1000);
        if (count($ids)) {
            $collection->addFieldToFilter('header_id', $ids);
        }
        $result = [];
        $result['items'] = [];
        
        $header = ['identifier', 'title', 'is_active', 'layout_xml', 'variables', 'custom_variables', 'parent', 'custom_fields', 'content'];
        $rows[] = $header;
        
        foreach ($collection->getItems() as $item) {
            try {
                if (!empty($this->_noExport['header'])) {
                    if (in_array($item->getData('identifier'), $this->_noExport['header'])) {
                        continue;
                    }
                }
                $item->setStoreId(0)->load($item->getId());
                $item->updateWorkspace(true);
                
                $itemData = [];
                foreach ($header as $column) {
                    $itemData[$column] = $item->getData($column);
                }
                $rows[] = $this->encodeData($itemData);
                
                $result['items'][] = [
                    'id'    => $item->getId(),
                    'name'  => $item->getData('title')
                ];
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_header.csv');
        }
        $this->csvReader->saveData($file, $rows);
        
        return $result;
    }
    
    public function exportFooter($file = null, $ids = [])
    {
        $collection = $this->footerModel->getCollection()->setPageSize(1000);
        if (count($ids)) {
            $collection->addFieldToFilter('footer_id', $ids);
        }
        $result = [];
        $result['items'] = [];
        
        $header = ['identifier', 'title', 'is_active', 'layout_xml', 'variables', 'custom_variables', 'parent', 'custom_fields', 'content'];
        $rows[] = $header;
        
        foreach ($collection->getItems() as $item) {
            try {
                if (!empty($this->_noExport['footer'])) {
                    if (in_array($item->getData('identifier'), $this->_noExport['footer'])) {
                        continue;
                    }
                }
                $item->setStoreId(0)->load($item->getId());
                $item->updateWorkspace(true);
                
                $itemData = [];
                foreach ($header as $column) {
                    $itemData[$column] = $item->getData($column);
                }
                $rows[] = $this->encodeData($itemData);
                
                $result['items'][] = [
                    'id'    => $item->getId(),
                    'name'  => $item->getData('title')
                ];
            } catch (\Exception $e) {
               die($e->getMessage());
            }
        }
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_footer.csv');
        }
        $this->csvReader->saveData($file, $rows);
        
        return $result;
    }
    
    public function exportCMSBlock($file = null, $ids = [])
    {
        $collection = $this->cmsBlockModel->getCollection()->setPageSize(1000)->setOrder('block_id', 'asc');
        if (count($ids)) {
            $collection->addFieldToFilter('block_id', $ids);
        }
        $result = [];
        $header = ['title', 'identifier', 'content', 'is_active'];
        $rows = [];
        $result['items'] = [];
        
        $rows[] = $header;
        foreach ($collection->getItems() as $item) {
            $itemData = [];
            foreach ($header as $column) {
                $itemData[$column] = $item->getData($column);
            }
            $rows[] = $this->encodeData($itemData);
            $result['items'][] = [
                'id'    => $item->getId(),
                'name'  => $item->getData('title')
            ];
        }
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/cms_block.csv');
        }
        $this->csvReader->saveData($file, $rows);
        return $result;
    }
    
    public function exportCMSPage($file = null, $ids = [])
    {
        $collection = $this->objectManager->get('\Magento\Cms\Model\Page')->getCollection()->setPageSize(1000)->setOrder('page_id', 'asc')
            ->addFieldToFilter('identifier', ['like' => '%codazon%']);
        if (count($ids)) {
            $collection->addFieldToFilter('page_id', $ids);
        }
        $result = [];
        $header = ['title', 'page_layout', 'meta_keywords', 'meta_description', 'identifier', 'content_heading', 'content', 'creation_time', 'update_time', 'is_active',
            'sort_order', 'layout_update_xml', 'custom_theme', 'custom_root_template', 'custom_layout_update_xml', 'custom_theme_from', 'custom_theme_to', 'meta_title'];
        $rows = [];
        $result['items'] = [];
        
        $rows[] = $header;
        foreach ($collection->getItems() as $item) {
            $itemData = [];
            foreach ($header as $column) {
                $itemData[$column] = $item->getData($column);
            }
            $rows[] = $this->encodeData($itemData);
            $result['items'][] = [
                'id'    => $item->getId(),
                'name'  => $item->getData('title')
            ];
        }
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/cms_page.csv');
        }
        $this->csvReader->saveData($file, $rows);
        return $result;
    }
    
    public function exportTemplateSet()
    {
        $collection = $this->objectManager->get('\Codazon\ThemeLayoutPro\Model\TemplateSet')->getCollection()->setPageSize(1000);
        $result = [];
        $header = ['template_set_id', 'template_set_name', 'template_set_image'];
        $rows = [];
        $result['items'] = [];
        
        $rows[] = $header;
        foreach ($collection->getItems() as $item) {
            $itemData = [];
            foreach ($header as $column) {
                $itemData[$column] = $item->getData($column);
                
            }
            $rows[] = $this->encodeData($itemData);
            $result['items'][] = [
                'id'    => $item->getId(),
                'name'  => $item->getData('template_set_name')
            ];
        }
        $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_template_set.csv');
        $this->csvReader->saveData($file, $rows);
        return $result;
    }
    
    
    public function exportTemplate()
    {
        $collection = $this->objectManager->get('\Codazon\ThemeLayoutPro\Model\Template')->getCollection()->setPageSize(1000);
        $result = [];
        $header = ['template_id', 'template_set_id', 'template_name', 'template_image', 'content'];
        $rows = [];
        $result['items'] = [];
        
        $rows[] = $header;
        foreach ($collection->getItems() as $item) {
            $itemData = [];
            foreach ($header as $column) {
                $itemData[$column] = $item->getData($column);
                
            }
            $rows[] = $this->encodeData($itemData);
            $result['items'][] = [
                'id'    => $item->getId(),
                'name'  => $item->getData('template_name')
            ];
        }
        $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_template.csv');
        $this->csvReader->saveData($file, $rows);
        return $result;
    }
    
    public function exportBlogCategories($file = null, $ids = [])
    {
        $collection = $this->objectManager->get('\Magefan\Blog\Model\Category')->getCollection()->setPageSize(1000);
        if (count($ids)) {
            $collection->addFieldToFilter('category_id', $ids);
        }
        $rows = (array)$collection->toArray();
        $rows = $rows['items'];
        $result['items'] = [];
        if (count($rows)) {
            $rows = array_merge([array_keys($rows[0])], $rows);

            foreach ($rows as $key => $row) {
                if ($key == 0) {
                    foreach ($row as $i => $attr) {
                        if ($attr == '_first_store_id') {
                            unset($rows[$key][$i]);
                        }
                        if ($attr == 'store_ids') {
                            unset($rows[$key][$i]);
                        }
                    }
                    continue;
                }
                unset($rows[$key]['_first_store_id']);
                unset($rows[$key]['store_ids']);
                $result['items'][] = [
                    'id'    => $row['category_id'],
                    'name'  => $row['title']
                ];
            }
        }
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/magefan_blog_category.csv');
        }
        $this->csvReader->saveData($file, $rows);
        return $result;
    }
    
    
    public function exportBlogPosts($file = null, $ids = [])
    {
        $collection = $this->objectManager->get('\Magefan\Blog\Model\Post')->getCollection()->setPageSize(1000);
        if (count($ids)) {
            $collection->addFieldToFilter('post_id', $ids);
        }
        $rows = (array)$collection->toArray();
        $rows = $rows['items'];
        $result['items'] = [];
        
        if (count($rows)) {
            $rows = array_merge([array_keys($rows[0])], $rows);

            foreach ($rows as $key => $row) {
                if ($key == 0) {
                    foreach ($row as $i => $attr) {
                        if ($attr == '_first_store_id') {
                            unset($rows[$key][$i]);
                        }
                        if ($attr == 'store_ids') {
                            unset($rows[$key][$i]);
                        }
                        if (!in_array('categories', $rows[$key]))
                            array_push($rows[$key], 'categories');
                        if (!in_array('tags', $rows[$key]))
                            array_push($rows[$key], 'tags');
                    }
                    continue;
                }

                unset($rows[$key]['_first_store_id']);
                unset($rows[$key]['store_ids']);
                foreach ($rows[0] as $attr) {
                    $rows[$key][$attr] = empty($rows[$key][$attr]) ? '': $rows[$key][$attr];
                }
                foreach ($rows[$key] as $attr => $value) {
                    if (!in_array($attr, $rows[0])) {
                        unset($rows[$key][$attr]);
                        continue;
                    }
                    if (is_array($value)) {
                        $rows[$key][$attr] = implode(',', $value);
                    }
                }
                
                $result['items'][] = [
                    'id'    => $row['post_id'],
                    'name'  => $row['title']
                ];
            }
        }
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/magefan_blog_post.csv');
        }
        $this->csvReader->saveData($file, $rows);
        return $result;
    }
    
    public function exportBlogTags($file = null, $ids = [])
    {
        $collection = $this->objectManager->get('\Magefan\Blog\Model\Tag')->getCollection()->setPageSize(1000);
        if (count($ids)) {
            $collection->addFieldToFilter('tag_id', $ids);
        }
        $rows = (array)$collection->toArray();
        $rows = $rows['items'];
        $result['items'] = [];
        
        if (count($rows)) {
            $rows = array_merge([array_keys($rows[0])], $rows);

            foreach ($rows as $key => $row) {
                if ($key == 0) {
                    continue;
                }

                foreach ($rows[0] as $attr) {
                    $rows[$key][$attr] = empty($rows[$key][$attr]) ? '': $rows[$key][$attr];
                }
                foreach ($rows[$key] as $attr => $value) {
                    if (!in_array($attr, $rows[0])) {
                        unset($rows[$key][$attr]);
                        continue;
                    }
                    if (is_array($value)) {
                        $rows[$key][$attr] = implode(',', $value);
                    }
                }
                
                $result['items'][] = [
                    'id'    => $row['tag_id'],
                    'name'  => $row['title']
                ];
            }
        }
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/magefan_blog_tag.csv');
        }
        $this->csvReader->saveData($file, $rows);
        return $result;
    }
}