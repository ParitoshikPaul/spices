<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Ajax;

class Templates extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $templateFactory;
    protected $templateSetFactory;
    protected $helper;
    protected $tplImgDir = 'codazon/themelayout/images/templates';
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Codazon\ThemeLayoutPro\Model\TemplateFactory $templateFactory,
        \Codazon\ThemeLayoutPro\Model\TemplateSetFactory $templateSetFactory,
        \Codazon\ThemeLayoutPro\Helper\Data $helper,
		array $data = []
    ) {
        parent::__construct($context);
        $this->resultJsonFactory    = $resultJsonFactory;
        $this->templateFactory      = $templateFactory;
        $this->templateSetFactory   = $templateSetFactory;
        $this->helper               = $helper;
    }
    
    protected function _getTemplateImage($image)
    {
        if ($image) {
            return $this->helper->getMediaUrl($this->tplImgDir . '/' . $image);
        }
        return '';
    }
    
    public function execute()
    {
        $result = [];
        $setCollection = $this->templateSetFactory->create()->getCollection();
        if ($setCollection->count()) {
            foreach($setCollection as $setItem) {
                $setId = $setItem->getId();
                $templateCollection = $this->templateFactory->create()->getCollection()
                    ->addFieldToFilter('template_set_id', $setId);
                
                $templates = [];
                if ($templateCollection->count()) {
                    foreach ($templateCollection as $template) {
                        $image = $this->_getTemplateImage($template->getData('template_image'));
                        $templates[] = [
                            'id'        => $template->getId(),
                            'name'      => $template->getData('template_name'),
                            'image'     => $image,
                            'content'   => $template->getData('content')
                        ];
                    }
                }
                $result[] = [
                    'id'        => $setId,
                    'name'      => $setItem->getData('template_set_name'),
                    'image'     => $setItem->getData('template_set_image'),
                    'templates' => $templates
                ];
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);
        return $resultJson;
    }
    
}