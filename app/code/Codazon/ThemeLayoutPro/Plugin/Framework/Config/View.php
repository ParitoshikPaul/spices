<?php
/**
 * Copyright © Codazon 2020, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Plugin\Framework\Config;

use Magento\Framework\App\ObjectManager;
use Codazon\ThemeLayoutPro\Model\CodazonTheme as ThemeModel;

class View
{   
    public function afterRead(
        \Magento\Framework\Config\View $view,
        $result
    ) {
        /* $objectManager = ObjectManager::getInstance();
        $helper = $objectManager->get(\Codazon\ThemeLayoutPro\Helper\Data::class);
        if (($helper->getCoreRegistry()->registry('themelayout_maincontent')) && ($currentTheme = $helper->getCurrentTheme())) {
            if (($currentTheme->getCode() == ThemeModel::DEFAULT_THEME_CODE) || ($currentTheme->getParentTheme()->getCode() == ThemeModel::DEFAULT_THEME_CODE)) {
                $images = $result['media']['Magento_Catalog']['images'];
                $images['swatch_image']['width'] = (float)$helper->getConfig('images/product/swatch_image_width');
                $images['swatch_image']['height'] = (float)$helper->getConfig('images/product/swatch_image_height');
                $images['swatch_thumb']['width'] = (float)$helper->getConfig('images/product/swatch_thumb_width');
                $images['swatch_thumb']['height'] = (float)$helper->getConfig('images/product/swatch_thumb_height');
                $result['media']['Magento_Catalog']['images'] = $images;
            }
        } */
        return $result;
    }
}
