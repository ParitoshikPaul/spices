<?php
/**
 * Copyright © 2018 Codazon. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Codazon\ThemeLayoutPro\Model\Config\Source;

class BlogPostListDisplay implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        	['value' => 'featured_img', 'label' => __('Featured Image')],
        	['value' => 'title', 'label' => __('Title')],
        	['value' => 'short_content', 'label' => __('Short Content')],
        	['value' => 'publish_time', 'label' => __('Published Date')],
			['value' => 'author', 'label' => __('Author')],
            ['value' => 'category', 'label' => __('Category')]
        ];
    }

    public function toArray()
    {
        return $this->toOptionArray();
    }
    
    
}