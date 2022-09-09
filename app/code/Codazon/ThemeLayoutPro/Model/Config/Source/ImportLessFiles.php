<?php
/**
 *
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Source;

use Magento\Framework\App\ObjectManager;

class ImportLessFiles implements \Magento\Framework\Option\ArrayInterface
{
    protected $_options;
    
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = $this->_getOptions();
        }
        return $this->_options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
    
    protected function _getOptions() {
        $model = ObjectManager::getInstance()->get('Codazon\ThemeLayoutPro\Model\MainContent');
        $fileList = $model->getFlexibleFileList();
        $options = [];
        foreach ($fileList as $file) {
            $options[] = [
                'value' => $file,
                'label' => $file
            ]; 
        }
        return $options;
    }
}