<?php
/**
 * Copyright © 2019 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ShoppingCartPro\Controller\Product\Wishlist;

class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
{
    public function execute()
    {
        $result = parent::execute();
        if ($this->session->isLoggedIn()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->getRequest()->setPostValue('form_key', $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class)->getFormKey());
            $wishlistController = $this->_objectManager->create(\Magento\Wishlist\Controller\Index\Add::class);
            $wishlistController->execute();
        }
        return $result;
    }
}