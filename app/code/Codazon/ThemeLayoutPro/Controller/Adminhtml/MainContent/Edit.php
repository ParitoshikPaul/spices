<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\MainContent;

use Magento\Backend\App\Action;

class Edit extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\ThemeLayoutAbstract
{
	/**
	* Core registry
	*
	* @var \Magento\Framework\Registry
	*/
	protected $_coreRegistry = null;
	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;
	
	public function __construct(
		Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Registry $registry
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->_coreRegistry = $registry;
		parent::__construct($context);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_ThemeLayoutPro::maincontent_edit');
    }
	protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codazon_ThemeLayoutPro::maincontent');
		return $resultPage;
	}
	public function execute()
	{
		$id = $this->getRequest()->getParam('entity_id');
		$model = $this->_objectManager->create('Codazon\ThemeLayoutPro\Model\MainContent');
		//$model->setMainContentId($id);
		
        if ($id) {
            $store = (int)$this->getRequest()->getParam('store');
			$model->setStoreId($store)->load($id);
			if (!$model->getId()) {
				$this->messageManager->addError(__('This maincontent no longer exists.'));
				/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
				$resultRedirect = $this->resultRedirectFactory->create();
				return $resultRedirect->setPath('*/*/');
			}
		}
	
		$data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}
	
		$this->_coreRegistry->register('themelayout_maincontent', $model);
	
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();
		$resultPage->addBreadcrumb(
			$id ? __('Edit Main Content') : __('New Main Content'),
			$id ? __('Edit Main Content') : __('New Main Content')
		);
		$resultPage->getConfig()->getTitle()->prepend(__('Main Content'));
		$resultPage->getConfig()->getTitle()
			->prepend($model->getId() ? $model->getThemelayoutTitle() : __('New Main Content'));
        if (!$id) {
            //print_r(get_class_methods($resultPage->getLayout())); die();
            $resultPage->getLayout()->unsetElement('store_switcher');
        }
		return $resultPage;
	}
}
