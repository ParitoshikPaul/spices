<?php
/**
 * Copyright © Codazon 2019, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Plugin\Product\Image;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\View\ConfigInterface;
use Magento\Catalog\Model\View\Asset\ImageFactory;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Codazon\ThemeLayoutPro\Helper\Data as ThemeHelper;
use Magento\Catalog\Helper\Image as ImageHelper;

class UrlBuilder
{
    protected $helper;
    
    protected $imageHelper;
    
    private $presentationConfig;

    private $viewAssetImageFactory;

    private $imageParamsBuilder;

    private $placeholderFactory;
    
    protected $needFilter = [
        //'product_swatch_image_large',
        'product_swatch_image_medium',
        //'product_swatch_image_small',
    ];
    
    public function __construct(
        ConfigInterface $presentationConfig,
        ParamsBuilder $imageParamsBuilder,
        ImageFactory $viewAssetImageFactory,
        PlaceholderFactory $placeholderFactory,
        ThemeHelper $helper,
        ImageHelper $imageHelper
    ) {
        $this->helper = $helper;
        $this->presentationConfig = $presentationConfig;
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
        $this->placeholderFactory = $placeholderFactory;
        $this->imageHelper = $imageHelper;
    }
    
    public function aroundGetUrl(
        \Magento\Catalog\Model\Product\Image\UrlBuilder $subject,
        \Closure $proceed,
        string $baseFilePath,
        string $imageDisplayArea
    ) {
        if (in_array($imageDisplayArea, $this->needFilter) && $this->helper->canUseConfig()
            && !($baseFilePath === null || $baseFilePath === 'no_selection')) {
            /* if ($imageDisplayArea == 'product_page_image_medium') {
                $width = (float)$this->helper->getConfig('images/product/product_base_image_width');
                $height = (float)$this->helper->getConfig('images/product/product_base_image_height');
            } elseif ($imageDisplayArea == 'product_page_image_small') {
                $width = (float)$this->helper->getConfig('images/product/product_moreview_image_width');
                $height = (float)$this->helper->getConfig('images/product/product_moreview_image_height');
            } */
            $request = $this->helper->getRequest();
            $width = (float)$request->getParam('cdz_custom_width') ? : (float)$this->helper->getProductImageSize('width');
            $height = (float)$request->getParam('cdz_custom_height') ? : (float)$this->helper->getProductImageSize('height');
            return $this->imageHelper->init(null, $imageDisplayArea)->setImageFile($baseFilePath)->resize($width, $height)->getUrl();
        }
        return $proceed($baseFilePath, $imageDisplayArea);
    }
}