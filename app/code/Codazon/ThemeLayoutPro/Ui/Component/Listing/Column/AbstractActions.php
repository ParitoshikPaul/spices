<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class AbstractActions extends Column
{
	/** Url path */
	protected $_editUrl = 'themelayoutpro/header/edit';
    /**
    * @var string
    */
	protected $_deleteUrl = 'themelayoutpro/header/delete';
    /**
    * @var string
    */
    protected $_primary = 'header_id';
    
	/** @var UrlInterface */
    protected $_urlBuilder;
    
    protected $_titleField = 'title';

	/**
	* @param ContextInterface $context
	* @param UiComponentFactory $uiComponentFactory
	* @param UrlInterface $urlBuilder
	* @param array $components
	* @param array $data
	*/
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		UrlInterface $urlBuilder,
		array $components = [],
		array $data = []
	) {
		$this->_urlBuilder = $urlBuilder;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}
	/**
	* Prepare Data Source
	*
	* @param array $dataSource
	* @return array
	*/
	public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as & $item) {
				$name = $this->getData('name');
				if (isset($item[$this->_primary])) {
					$item[$name]['edit'] = [
						'href' => $this->_urlBuilder->getUrl($this->_editUrl, [$this->_primary => $item[$this->_primary]]),
						'label' => __('Edit')
					];
					$item[$name]['delete'] = [
						'href' => $this->_urlBuilder->getUrl($this->_deleteUrl, [$this->_primary => $item[$this->_primary]]),
						'label' => __('Delete'),
						'confirm' => [
							'title' => __('Delete "${ $.$data.' . $this->_titleField . ' }"'),
							'message' => __('Are you sure you wan\'t to delete a "${ $.$data.' . $this->_titleField . ' }" record?')
						]
					];
				}
			}
		}
		return $dataSource;
	}
}
