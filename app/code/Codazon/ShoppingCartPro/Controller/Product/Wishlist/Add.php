<?php
/**
 * Copyright © 2019 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\ShoppingCartPro\Controller\Product\Wishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;
    
    protected $redirectUrl;

    protected $resultLayoutFactory;
    
    /**
     * @param Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        Validator $formKeyValidator
    ) {
        
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        $this->productRepository = $productRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultLayoutFactory = $resultLayoutFactory;
        
    }
    
    public function execute()
    {
        $postResult = [
            'success'   =>  false,
        ];
        
        if (!$this->_customerSession->isLoggedIn()) {
            if ($currentUrl = $this->getRequest()->getParam('currentUrl')) {
                //$this->getRequest()->setParam('referer', $this->_objectManager->get('Magento\Framework\Url\EncoderInterface')->encode($currentUrl));
            }
            $layout = $this->resultLayoutFactory->create();
            $layout->addHandle(['ajax_wishlist']);
            $postResult['login_form_html'] = $layout->getLayout()->getOutput();
            //$postResult['login_url'] = $this->_objectManager->get(\Magento\Framework\UrlInterface::class)->getUrl('ajaxpost/product_wishlist/loginPost');
            $postResult['after_login_url'] = $this->_objectManager->get(\Magento\Framework\UrlInterface::class)->getUrl('ajaxpost/product_wishlist/add');
            return $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($postResult)
            );
        }
        
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        
        $requestParams = $this->getRequest()->getParams();
        $session = $this->_customerSession;
        if ($session->getBeforeWishlistRequest()) {
            $requestParams = $session->getBeforeWishlistRequest();
            $session->unsBeforeWishlistRequest();
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!empty($requestParams['referer'])) {
            $this->redirectUrl = $this->_objectManager->get('Magento\Framework\Url\DecoderInterface')->decode($requestParams['referer']);
        }
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $postResult['message'] = __('Your session has expired.');
            return $this->returnResult($postResult);
        }        
        
        $wishlist = $this->wishlistProvider->getWishlist();
        
        if (!$wishlist) {
            $postResult['message'] = __('We can\'t add the item to Wish List right now.');
            return $this->returnResult($postResult);
        }

        

        

        $productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;
        if (!$productId) {
            $postResult['message'] = __('We can\'t specify a product.');
            return $this->returnResult($postResult);
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $postResult['message'] = __('We can\'t specify a product.');
            return $this->returnResult($postResult);
        }

        try {
            $buyRequest = new \Magento\Framework\DataObject($requestParams);
            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
            if ($wishlist->isObjectNew()) {
                $wishlist->save();
            }
            $this->_eventManager->dispatch(
                'wishlist_add_product',
                ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
            );

            if (!$this->redirectUrl) {
                $referer = $session->getBeforeWishlistUrl();
                if ($referer) {
                    $session->setBeforeWishlistUrl(null);
                } else {
                    $referer = $this->_redirect->getRefererUrl();
                }
            }

            $this->_objectManager->get(\Magento\Wishlist\Helper\Data::class)->calculate();            
            $postResult['message'] = __('%1 has been added to your Wish List.', $product->getName());
            $postResult['success'] = true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add the item to Wish List right now.')
            );
        }
        
        return $this->returnResult($postResult);
    }
    
    protected function returnResult($postResult) {
        if ($this->redirectUrl) {
            if ($postResult['success']) {
                $this->messageManager->addSuccessMessage($postResult['message']);
            } else {
                $this->messageManager->addErrorMessage($postResult['message']);
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->redirectUrl);
            return $resultRedirect;
        }
        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($postResult)
        );
    }
}