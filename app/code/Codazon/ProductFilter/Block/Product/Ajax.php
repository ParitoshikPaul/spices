<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ProductFilter\Block\Product;
/**
 * Catalog Products List Ajax block
 * Class ProductsList
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Ajax extends \Magento\Framework\View\Element\Template
{
	const PAGE_VAR_NAME = 'np';
    
    protected $productsListBlock;
    
    protected $helper;
	
    public function __construct(
		\Magento\Catalog\Block\Product\Context $context,
		\Codazon\ProductFilter\Block\Product\ProductsList $productsListBlock,
		\Codazon\ProductFilter\Block\ImageBuilderFactory $customImageBuilderFactory,
        \Codazon\Core\Helper\Data $helper,
        array $data = [] 
    ) {
		$this->productsListBlock = $productsListBlock;
		$this->customImageBuilderFactory = $customImageBuilderFactory;
        $this->helper = $helper;
		parent::__construct(
			$context,
			$data
        );
    }
	
	protected function _toHtml(){
		$data = $this->getRequest()->getParams();
		$data['custom_template'] = $data['custom_template'];
        $data['is_next_page'] = 1;
		$data['products_per_page'] = $data['products_count'];
		$this->getRequest()->setParam(self::PAGE_VAR_NAME, $data['cur_page']);
        $data['cache_key_info'] = [$data['cache_key_info'][0].'_full_html_page'.$this->getRequest()->getParam(self::PAGE_VAR_NAME, 1)];
        $productBlock = $this->productsListBlock->setData($data);
		$productBlock->getLayout()->getBlock('product.price.render.default')
			->setData('price_render_handle','catalog_product_prices')
			->setData('use_link_for_as_low_as', true);
		$result['last_page'] = $productBlock->createCollection()->getLastPageNumber();
		if($result['last_page'] >= $data['cur_page']){
			$result['html'] = $this->helper->minifyHtml($productBlock->toHtml());
		}else{
			$result['html'] = '';
		}
		return json_encode($result);
	}
	
	public function getImage($product, $imageId, $attributes = [])
    {
    	$data = $this->getRequest()->getParams();
        $width = $data['thumb_width'];
        $height = $data['thumb_height'];
        $attributes = array('width' => $width,'height' => $height);
        $imageBuilder = $this->customImageBuilderFactory->create();
        return $imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
        return $html;
    }
}
