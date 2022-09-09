<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Block;

use Magento\Customer\Model\Context;

class ProductTabs extends LayoutAbstract
{
    protected function _prepareLayout()
    {
        $this->helper->addProductCustomTabs($this->getLayout());
        return parent::_prepareLayout();
    }
}