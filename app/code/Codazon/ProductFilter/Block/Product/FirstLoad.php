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
class FirstLoad extends \Magento\Framework\View\Element\Template
{
	const PAGE_VAR_NAME = 'np';
	    
    protected $cacheKeyInfo;
    
    protected $objectManager;
    
    protected $helper;
    
	public function __construct(
		\Magento\Catalog\Block\Product\Context $context,
        \Codazon\Core\Helper\Data $helper,
        array $data = []
    ) {
		parent::__construct(
			$context,
			$data
        );
        $this->helper = $helper;
        $this->objectManager = $helper->getObjectManager();
        $this->addData([
            'cache_tags' => ['CDZ_PRODUCT_FILTER_FIRSTLOAD']
		]);
    }
    
    public function getCacheKeyInfo()
    {
        if ($this->cacheKeyInfo === null) {
            if ($cacheKeyInfo = $this->getRequest()->getParam('cache_key_info')) {
                $this->cacheKeyInfo = [$cacheKeyInfo[0].'_full_html_page'.$this->getRequest()->getParam(self::PAGE_VAR_NAME, 1)];
            } else {
                $this->cacheKeyInfo = [
                    'CDZ_PRODUCT_FILTER_FIRSTLOAD',
                    $this->_storeManager->getStore()->getId(),
                    $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
                    $this->objectManager->get('Magento\Framework\App\Http\Context')->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
                    intval($this->getRequest()->getParam(self::PAGE_VAR_NAME, 1)),
                    json_encode($this->getRequest()->getParams())
                ];
            }
        }
        return $this->cacheKeyInfo;
    }
    
    public function getIdentities()
    {
        return ['CDZ_PRODUCT_FILTER_FIRSTLOAD' . '_' . $this->getData('cache_string')];
    }
    
	protected function _toHtml(){
        parent::_toHtml();
		$data = $this->getRequest()->getParams();
        if (!empty($data['block_groups'])) {
            $html = [];
            $layout = $this->getLayout();
            $gData = json_decode($data['block_groups'], true);
            $html = '';
            foreach ($gData as $key => $gD) {
                $gD['cache_key_info'] = [$gD['cache_key_info'][0].'_full_html_page'.$this->getRequest()->getParam(self::PAGE_VAR_NAME, 1)];
                $productBlock = $layout->createBlock(\Codazon\ProductFilter\Block\Product\ProductsList::class)->addData($gD);
                $html .= '<textarea id="pd-' . $key . '" style="display:none">' . $this->helper->minifyHtml($productBlock->toHtml()) . '</textarea>';
            }
            return $html;
        } else {
            $data['cache_key_info'] = [$data['cache_key_info'][0].'_full_html_page'.$this->getRequest()->getParam(self::PAGE_VAR_NAME, 1)];
            $productBlock = $this->getLayout()->createBlock(\Codazon\ProductFilter\Block\Product\ProductsList::class)->addData($data);
            return $this->helper->minifyHtml($productBlock->toHtml());
        }
	}
}
