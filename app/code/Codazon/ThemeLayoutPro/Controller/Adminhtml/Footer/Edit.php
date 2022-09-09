<?php
/**
 *
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Footer;

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
        return $this->_authorization->isAllowed('Codazon_ThemeLayoutPro::footer_edit');
    }
	protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codazon_ThemeLayoutPro::footer');
		return $resultPage;
	}
	public function execute()
	{
		$id = $this->getRequest()->getParam('footer_id');
		$model = $this->_objectManager->create('Codazon\ThemeLayoutPro\Model\Footer');
		
        if ($store = (int)$this->getRequest()->getParam('store')) {
            $model->setData('store_id', $store);
        }
        
        if ($id) {
			$model->load($id);
            
			if (!$model->getId()) {
				$this->messageManager->addError(__('This footer no longer exists.'));
				/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
				$resultRedirect = $this->resultRedirectFactory->create();
				return $resultRedirect->setPath('*/*/');
			}
		}
        
        if ($store = (int)$this->getRequest()->getParam('store')) {
            $model->setData('store_id', $store);
        }
        
		$data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}
	
		$this->_coreRegistry->register('themelayout_footer', $model);
	
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();
		$resultPage->addBreadcrumb(
			$id ? __('Edit Footer') : __('New Footer'),
			$id ? __('Edit Footer') : __('New Footer')
		);
		$resultPage->getConfig()->getTitle()->prepend(__('Footer'));
		$resultPage->getConfig()->getTitle()
			->prepend($model->getId() ? $model->getTitle() : __('New Footer'));
	
		return $resultPage;
	}
}
