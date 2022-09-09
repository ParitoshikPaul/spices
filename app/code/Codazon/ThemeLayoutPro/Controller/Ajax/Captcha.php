<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\ThemeLayoutPro\Controller\Ajax;

class Captcha extends \Magento\Framework\App\Action\Action
{  
	protected $helper;   
	public function __construct(
        \Magento\Framework\App\Action\Context $context
    ) {
		parent::__construct($context);
    }
    
    public function execute()
    {
        $helper = $this->_objectManager->get(\Codazon\Core\Helper\Data::class);
        $captcha = $this->_objectManager->create(\Magento\Captcha\Block\Captcha::class);
        $captcha->setFormId('user_login')->setImgWidth(230)->setImgHeight(50);
        $html = $captcha->setData('cache_lifetime', false)->toHtml();
        echo $html ? $html."<br />" : '';
        
        if ($helper->getConfig('recaptcha_frontend/type_for/customer_login')) {
            $captcha = $this->_objectManager->create(\Magento\ReCaptchaUi\Block\ReCaptcha::class,
            ['data' => [
                    'recaptcha_for' => 'customer_login',
                    'jsLayout' => [
                        'components' => [
                            'recaptcha' => [
                                'component' => 'Magento_ReCaptchaFrontendUi/js/reCaptcha'
                            ]
                        ]
                    ]
                ]
            ])->addData([
                'recaptcha_id'  => uniqid('recaptcha-'),
            ])->setTemplate('Magento_ReCaptchaFrontendUi::recaptcha.phtml');
            $html = $captcha->toHtml();
            echo $html;
        }
        die();
    }
}