<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class Import extends \Magento\Framework\Model\AbstractModel
{
    protected $helper;
    
    protected $mainContentModel;
    
    protected $headerModel;
    
    protected $footerModel;
    
    protected $storeManager;
    
    protected $fixtureManager;
    
    protected $csvReader;
    
    protected $cmsBlockModel;
    
    protected $objectManager;
    
    protected $io;
    
    protected $fileSystem;
    
    protected $directoryList;
    
    protected $mageFileSystem;
    
    protected $magentoVersion;
    
    protected $quoteMask = '~~~';
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Codazon\ThemeLayoutPro\Model\Header $headerModel,
        \Codazon\ThemeLayoutPro\Model\Footer $footerModel,
        \Codazon\ThemeLayoutPro\Model\MainContent $mainContentModel,
        \Magento\Cms\Model\Block $cmsBlockModel,
        \Codazon\ThemeLayoutPro\Helper\Data $helper,
        SampleDataContext $sampleDataContext
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
    }
    
    public function decodeData($value) {
        return str_replace($this->quoteMask, '"', $value);
    }
    
    public function getMagentoVersion()
    {
        if ($this->magentoVersion === null) {
            $this->magentoVersion = str_replace(
                ['-dev', 'dev-', '-develop', 'develop-'], ['', '', '', ''],
                $this->objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion()
            );
        }
        return $this->magentoVersion;
        
    }
    
    protected function isMagento22x()
    {
        $version = $this->getMagentoVersion();
        return version_compare($version, '2.2.0', '>=');
    }
    
    public function importData()
    {
        try {
            $this->objectManager->get('Magento\Framework\App\State')->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            
        }
        try {
            $this->importTemplateSet();
            $this->importTemplate();
            $this->importHeader();
            $this->importMainContent();
            $this->importFooter();
            $this->importCMSBlock();
            $this->importCMSPage();
            $this->importBlogCategory();
            $this->importBlogTag();
            $this->importBlogPost();
            $this->importCmsPageAmp();
        } catch (\Exceptions $e) {
            
        }
    }
    
    
    public function importHeader($identifier = null, $file = null)
    {
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_header.csv');
        }
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Codazon\ThemeLayoutPro\Model\HeaderFactory');

        if ($identifier) {
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $this->decodeData($value);
                }
                $item = $factory->create()->setStoreId(0);
                if ($identifier == $data['identifier']) {
                    if ($item->load($data['identifier'], 'identifier')->getId()) {
                         break;
                    }
                    $item->addData($data);
                    $item->save();
                    $item->unsetData();
                    break;
                }
            }
        } else {
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $this->decodeData($value);
                }
                $item = $factory->create()->setStoreId(0);
                if ($item->load($data['identifier'], 'identifier')->getId()) {
                    continue;
                }
                $item->addData($data);
                $item->save();
                $item->unsetData();
            }
        }
    }
    
    public function importFooter($identifier = null, $file = null)
    {
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_footer.csv');
        }
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Codazon\ThemeLayoutPro\Model\FooterFactory');
        
        if ($identifier) {
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $this->decodeData($value);
                }
                $item = $factory->create()->setStoreId(0);
                if ($identifier == $data['identifier']) {
                    if ($item->load($data['identifier'], 'identifier')->getId()) {
                         break;
                    }
                    $item->addData($data);
                    $item->save();
                    $item->unsetData();
                    break;
                }
            }
        } else {
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $this->decodeData($value);
                }
                $item = $factory->create()->setStoreId(0);
                if ($item->load($data['identifier'], 'identifier')->getId()) {
                    continue;
                }
                $item->addData($data);
                $item->save();
                $item->unsetData();
            }
        }
    }
    
    public function importMainContent($identifier = null, $file = null)
    {
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_maincontent_entity.csv');
        }
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Codazon\ThemeLayoutPro\Model\MainContentFactory');

        $serializeConditionHelper = $this->objectManager->get('\Codazon\ThemeLayoutPro\Helper\SerializedConditions');
        $jsonConditionHelper = $this->objectManager->get('\Codazon\ThemeLayoutPro\Helper\JsonConditions');
        
        if ($identifier) {
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $this->decodeData($value);
                }
                if ($identifier == $data['identifier']) {
                    $item = $factory->create()->setStoreId(0);
                    if ($item->getCollection()->addFieldToFilter('identifier', $data['identifier'])->count()) {
                        break;
                    }
                    
                    if (!$this->isMagento22x()) {
                        $needReplace = [];
                        $pattern = '/conditions_encoded=([.\\\]+)"(.*?)([.\\\]+)\"/si';
                        if (preg_match_all($pattern, $data['themelayout_content'], $constructions, PREG_SET_ORDER)) {
                            foreach($constructions as $index => $construction) {
                                $needReplace[] = $construction[2];
                            }
                        }
                        $needReplace = array_unique($needReplace);
                        foreach ($needReplace as $replace) {
                            $condition = $serializeConditionHelper->encode($jsonConditionHelper->decode($replace));
                            $data['themelayout_content'] = str_replace($replace, $condition, $data['themelayout_content']);
                        }
                    }
                    
                    $item->addData($data);
                    $item->save();
                    $item->unsetData();
                    break;
                }
            }
        } else {
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $this->decodeData($value);
                }
                
                $item = $factory->create()->setStoreId(0);
                if ($item->getCollection()->addFieldToFilter('identifier', $data['identifier'])->count()) {
                    continue;
                }
                
                if (!$this->isMagento22x()) {
                    $needReplace = [];
                    $pattern = '/conditions_encoded=([.\\\]+)"(.*?)([.\\\]+)\"/si';
                    if (preg_match_all($pattern, $data['themelayout_content'], $constructions, PREG_SET_ORDER)) {
                        foreach($constructions as $index => $construction) {
                            $needReplace[] = $construction[2];
                        }
                    }
                    $needReplace = array_unique($needReplace);
                    foreach ($needReplace as $replace) {
                        $condition = $serializeConditionHelper->encode($jsonConditionHelper->decode($replace));
                        $data['themelayout_content'] = str_replace($replace, $condition, $data['themelayout_content']);
                    }
                }
                
                $item->addData($data);
                $item->save();
                $item->unsetData();
            }
        }
    }
    
    public function fixTemplateEncodedIssue() {
        $collection = $this->objectManager->get('\Codazon\ThemeLayoutPro\Model\Template')->getCollection()->setPageSize(1000);
        
        $serializeConditionHelper = $this->objectManager->get('\Codazon\ThemeLayoutPro\Helper\SerializedConditions');
        $jsonConditionHelper = $this->objectManager->get('\Codazon\ThemeLayoutPro\Helper\JsonConditions');
        
        foreach ($collection->getItems() as $item) {
            $content = $item->getData('content');
            $needReplace = [];
            $pattern = '/conditions_encoded="(.*?)\"/si';
            if (preg_match_all($pattern, $content, $constructions, PREG_SET_ORDER)) {
                foreach($constructions as $index => $construction) {
                    $needReplace[] = $construction[1];
                }
            }
            $needReplace = array_unique($needReplace);
            foreach ($needReplace as $replace) {
                $condition = $jsonConditionHelper->encode($serializeConditionHelper->decode($replace));
                $content = str_replace($replace, $condition, $content);
            }
            $item->setData('content', $content);
            $item->save();
        }
    }
    
    
    
    public function importTemplateSet()
    {
        $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_template_set.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Codazon\ThemeLayoutPro\Model\TemplateSetFactory');

        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $this->decodeData($value);
            }
            
            $item = $factory->create();
            $item->load($data['template_set_id']);
            if (!$item->getId()) {
                $item->unsetData();
            }
            unset($data['template_set_id']);
            $item->addData($data);
            $item->save();
            
        }
    }
    
    public function importTemplate()
    {
        $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/themelayout_template.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Codazon\ThemeLayoutPro\Model\TemplateFactory');
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $this->decodeData($value);
            }
            if (!$this->isMagento22x()) {
                $needReplace = [];
                $pattern = '/conditions_encoded=([.\\\]+)"(.*?)([.\\\]+)\"/si';
                if (preg_match_all($pattern, $data['content'], $constructions, PREG_SET_ORDER)) {
                    foreach($constructions as $index => $construction) {
                        $needReplace[] = $construction[2];
                    }
                }
                $needReplace = array_unique($needReplace);
                foreach ($needReplace as $replace) {
                    $condition = $serializeConditionHelper->encode($jsonConditionHelper->decode($replace));
                    $data['content'] = str_replace($replace, $condition, $data['content']);
                }
            }
            $item = $factory->create();
            $item->load($data['template_id']);
            if (!$item->getId()) {
                $item->unsetData();
            }
            unset($data['template_id']);
            $item->addData($data);
            $item->save();
            $item->unsetData();
        }
    }
    
    public function importCMSBlock($identifier = null, $file = null)
    {
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/cms_block.csv');
        }
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Magento\Cms\Model\BlockFactory');
        if ($identifier) {
            //$file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/cms_block.csv');
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $this->decodeData($value);
                }
                if ($data['identifier'] == $identifier) {
                    $item = $factory->create();
                    if (!($item->load($data['identifier'], 'identifier')->getId())) {
                        $item->setStoreId(0);
                        $item->addData($data);
                        $item->save();
                    }
                    break;
                }
            }
        } else {
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $this->decodeData($value);
                }
                $item = $factory->create();
                if ($item->load($data['identifier'], 'identifier')->getId()) {
                    continue;
                }
                try {
                    //$item->setStoreId(0);
                    $item->addData($data);
                    $item->save();
                    $item->unsetData();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    
                } catch (\Magento\Framework\Exception\SessionException $e) {
                    
                }
            }
        }
    }
    
    public function importCMSPage()
    {
        $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/cms_page.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Magento\Cms\Model\PageFactory');
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $this->decodeData($value);
            }
            $item = $factory->create();
            if ($item->load($data['identifier'], 'identifier')->getId()) {
                continue;
            }
            $item->setStoreId(0);
            $item->addData($data);
            $item->save();
            $item->unsetData();
        }
    }
    
    public function importCmsPageAmp()
    {
        if (class_exists('\Codazon\GoogleAmpManager\Helper\Import')) {
            $this->objectManager->get(\Codazon\GoogleAmpManager\Helper\Import::class)->importCmsPageAmp();
        }
    }
    
    public function importBlogCategory($file = null)
    {
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/magefan_blog_category.csv');
        }
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Magefan\Blog\Model\CategoryFactory');
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $this->decodeData($value);
            }
            $item = $factory->create();
            
            if ($item->load($data['category_id'])->getId()) {
                continue;
            }
            //$item->setId($data['category_id']);
            unset($data['category_id']);
            $item->addData($data);
            try {
                $item->save();
                $item->unsetData();
            } catch (\Exceptions $e) {
                
            }
        }
    }
    
    public function importBlogTag($file = null)
    {
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/magefan_blog_tag.csv');
        }
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Magefan\Blog\Model\TagFactory');
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $this->decodeData($value);
            }
            $item = $factory->create();
            
            if ($item->load($data['tag_id'])->getId()) {
                continue;
            }
            //$item->setId($data['tag_id']);
            unset($data['tag_id']);
            $item->addData($data);
            try {
                $item->save();
                $item->unsetData();
            } catch (\Exceptions $e) {
                
            }
        }
    }
    
    public function importBlogPost($file = null)
    {
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_ThemeLayoutPro::fixtures/magefan_blog_post.csv');
        }
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Magefan\Blog\Model\PostFactory');
        $categoryFactory = $this->objectManager->get('\Magefan\Blog\Model\CategoryFactory');
        $tagFactory = $this->objectManager->get('\Magefan\Blog\Model\TagFactory');
        
        $helloworld = $factory->create()->load('hello-world', 'identifier');
        if ($helloworld->getId()) {
            $helloworld->delete();
        }

        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $this->decodeData($value);
            }
            $item = $factory->create();
            if ($item->load($data['post_id'])->getId()) {
                continue;
            }
            //$item->setId($data['post_id']);
            unset($data['post_id']);
            
            $tags = explode(',', $data['tags']);
            foreach ($tags as $key => $tag) {
                if (!($tagFactory->create()->load($tag)->getId())) {
                    unset($tags[$key]);
                }
            }
            $data['tags'] = implode(',', $tags);
            
            $categories = explode(',', $data['categories']);
            foreach ($categories as $key => $category) {
                if (!($categoryFactory->create()->load($category)->getId())) {
                    unset($categories[$key]);
                }
            }
            $data['categories'] = implode(',', $categories);
            
            $item->addData($data);
            $item->save();
            $item->unsetData();
        }
    }
    
    public function importMenu($identifier = null, $file = null)
    {
        if ($file === null) {
            $file = $this->fixtureManager->getFixture('Codazon_MegaMenu::fixtures/codazon_megamenu.csv');
        }
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $factory = $this->objectManager->get('\Codazon\MegaMenu\Model\MegamenuFactory');
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $this->decodeData($value);
            }
            if ($identifier) {
                if ($identifier != $data['identifier']) {
                    continue;
                }
            }
            $item = $factory->create();
            if ($item->load($data['identifier'], 'identifier')->getId()) {
                continue;
            }
            $item->addData($data);
            $item->setIsActive(1);
            try {
                $item->save();
                $item->unsetData();
            } catch (\Exceptions $e) {
                
            }
            if ($identifier && ($identifier === $data['identifier'])) {
                break;
            }
        }
    }
}