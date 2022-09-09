<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\Component\Listing\Column;

class Image extends AbstractActions
{
    protected $_itemKey = 'item_object';
    protected $_primary = 'header_id';
    protected $_itemClass = 'Codazon\ThemeLayoutPro\Model\ThemeLayoutAbstract';
    protected $_assetDir = 'codazon/themelayout/header';
    protected $_noImage = 'codazon/themelayout/images/no-preview.jpg';
    
    public function prepareDataSource(array $dataSource)
    {	
   	    if (isset($dataSource['data']['items'])) {
			
			$repository = $this->_objectManager->get('Magento\Framework\View\Asset\Repository');
            $fieldName = $this->getData('name');
            $object = $this->_objectManager->get($this->_itemClass);
            foreach ($dataSource['data']['items'] as & $item) {
				$directory = $item['identifier'];
                $file = $this->_assetDir.'/'.$directory.'/preview.jpg';
                if ($object->mediaFileExists($file, true)) {
                    $image = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) 
                        . $file;
                } else {
                    $image = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA])
                        . $this->_noImage;
                }
                $item[$fieldName . '_src'] = $image;
                $item[$fieldName . '_alt'] = $item['title'];
                $item[$fieldName . '_link'] = $this->_urlBuilder->getUrl($this->_editUrl,
                    [$this->_primary => $item[$this->_primary]]);
                $item[$fieldName . '_orig_src'] = $image;
            }
        }
        return $dataSource;
    }
    
}
