<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\Component\Listing\Column;

class FooterImage extends Image
{
    protected $_itemKey = 'item_object';
    protected $_primary = 'footer_id';
    protected $_itemClass = 'Codazon\ThemeLayoutPro\Model\ThemeLayoutAbstract';
    protected $_assetDir = 'codazon/themelayout/footer';
    protected $_noImage = 'codazon/themelayout/images/no-preview.jpg';
}
