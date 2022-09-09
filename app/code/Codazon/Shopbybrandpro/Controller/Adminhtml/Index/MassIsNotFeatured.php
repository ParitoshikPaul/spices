<?php
/**
 *
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Controller\Adminhtml\Index;

class MassIsNotFeatured extends \Codazon\Shopbybrandpro\Controller\Adminhtml\MassStatusAbstract
{
    protected $primary = 'option_id';
     
    protected $modelClass = 'Codazon\Shopbybrandpro\Model\Brand';
    
    protected $fieldName = 'brand_is_featured';
    
    protected $fieldValue = 0;
    
    protected $successText = 'Your selected items have been disable for "Is Featured" field.';
}