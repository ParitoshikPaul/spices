<?php
/**
 * Copyright © 2020 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Model\Category;
 
use Magento\Catalog\Model\Category\DataProvider as CategoryDataProvider;
use Codazon\ThemeLayoutPro\Model\Category\Attribute\Backend\Image as CodazonImageBackendModel;
use Magento\Framework\App\ObjectManager;
 
class DataProvider extends CategoryDataProvider
{
    private $fileInfo;
    
    private $categoryImage;
    
    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'cdz_thumbnail_image';
 
        return $fields;
    }
    
    public function getData()
    {
        
        $this->loadedData = parent::getData();
        $category = $this->getCurrentCategory();
        if ($category) {
            $categoryData = $this->loadedData[$category->getId()];
            $this->loadedData[$category->getId()] = $this->convertAdditionalValues($category, $categoryData);
        }
        
        return $this->loadedData;
    }
    
    protected function prepareObjects()
    {
        if ($this->fileInfo === null) {
            $this->fileInfo = ObjectManager::getInstance()->get(\Magento\Catalog\Model\Category\FileInfo::class);
        }
        if ($this->categoryImage === null) {
            if (class_exists('\Magento\Catalog\Model\Category\Image')) {
                $this->categoryImage = ObjectManager::getInstance()->get(\Magento\Catalog\Model\Category\Image::class);
            } else {
                $this->categoryImage = false;
            }
        } 
        
        return $this;
    }
    
    private function convertAdditionalValues($category, $categoryData)
    {
        $this->prepareObjects();
        
        foreach ($category->getAttributes() as $attributeCode => $attribute) {
            if (!isset($categoryData[$attributeCode])) {
                continue;
            }
            if ($attribute->getBackend() instanceof CodazonImageBackendModel) {
                
                unset($categoryData[$attributeCode]);

                $fileName = $category->getData($attributeCode);

                if ($this->fileInfo->isExist($fileName)) {
                    
                    $stat = $this->fileInfo->getStat($fileName);
                    $mime = $this->fileInfo->getMimeType($fileName);

                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $categoryData[$attributeCode][0]['name'] = basename($fileName);

                    $categoryData[$attributeCode][0]['url'] = $this->categoryImage ? $this->categoryImage->getUrl($category, $attributeCode) : 
                        $category->getImageUrl($attributeCode);

                    $categoryData[$attributeCode][0]['size'] = isset($stat) ? $stat['size'] : 0;
                    $categoryData[$attributeCode][0]['type'] = $mime;
                }
            }
        }
        return $categoryData;
    }
}