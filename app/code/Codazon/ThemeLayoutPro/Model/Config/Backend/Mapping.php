<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Model\Config\Backend;

class Mapping extends \Codazon\ThemeLayoutPro\Model\Config\ThemeConfigValue
{
    protected $_customtabs = null;
    
    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $arr = unserialize($value);
        $this->setValue($arr);
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        unset($value['__empty']);
        $arr = serialize($value);
        $this->setValue($arr);
    }
}