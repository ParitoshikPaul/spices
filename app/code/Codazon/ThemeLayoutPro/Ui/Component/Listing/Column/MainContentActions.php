<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\Component\Listing\Column;

class MainContentActions extends \Codazon\ThemeLayoutPro\Ui\Component\Listing\Column\AbstractActions
{
	/** Url path */
	protected $_editUrl = 'themelayoutpro/maincontent/edit';
    /**
    * @var string
    */
	protected $_deleteUrl = 'themelayoutpro/maincontent/delete';
    /**
    * @var string
    */
    protected $_primary = 'entity_id';
    
    protected $_titleField = 'themelayout_title';
}
