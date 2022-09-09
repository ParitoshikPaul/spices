<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ThemeLayoutPro\Model\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $model;
	public function __construct(\Codazon\ThemeLayoutPro\Model\ThemeLayoutAbstract $model)
    {
        $this->model = $model;
    }
	public function toOptionArray()
	{
		$options[] = ['label' => '', 'value' => ''];
		$availableOptions = $this->model->getAvailableStatuses();
		foreach ($availableOptions as $key => $value) {
			$options[] = [
				'label' => $value,
				'value' => $key,
			];
		}
		return $options;
	}
}
