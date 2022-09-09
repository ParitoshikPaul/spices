<?php
/**
 * Copyright © 2020 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\MegaMenu\Block\Widget;

class Categorieslist extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	protected function _toHtml(){		
		
        $parentId = (int)str_replace('category/','',$this->getData('parent_id'));
        $categoriesTree = $this->getLayout()->createBlock(\Codazon\MegaMenu\Block\Widget\CategoriesTree::class)
            ->assignUlHtmlClass($this->getData('ul_html_class'))
            ->assignLiHtmlClass($this->getData('li_html_class'))
            ->setData('parent_id',$parentId);
        if($this->getData('item_count')){
			$categoriesTree->setData('item_count',$this->getData('item_count'));
		}
		return $this->getData('show_wrap') ?
            '<ul class="'.$this->getData('wrap_html_class').'">'.$categoriesTree->getHtml('', 'submenu', 0).'</ul>' :
            $categoriesTree->getHtml('', 'submenu', 0);
	}
}