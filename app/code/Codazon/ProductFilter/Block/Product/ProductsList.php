<?php
/**
 * Copyright © 2020 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ProductFilter\Block\Product;

class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    protected $urlHelper;
    
    protected $productCollectionFactory;
    
    protected $_filterData = [];
    
    protected $_sliderData = [];
    
    protected $_show = [];
    
    protected $listProduct;
    
    protected $objectManager;
    
    protected $productLazyImg = true;
    
    protected $themeHelper;
    
    public function getObjectManager()
    {
        if ($this->objectManager === null) {
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }
        return $this->objectManager;
    }
    
    public function getUrlHelper()
    {
        if ($this->urlHelper === null) {
            $this->urlHelper = $this->getObjectManager()->get(\Magento\Framework\Url\Helper\Data::class);
        }
        return $this->urlHelper;
    }
    
    public function getListProduct()
    {
        if ($this->listProduct === null) {
            $this->listProduct = $this->getObjectManager()->get(\Magento\Catalog\Block\Product\ListProduct::class);
        }
        return $this->listProduct;
    }
    
    public function getCacheKeyInfo()
    {
        if (!$this->hasData('cache_key_info')) {
            $data = $this->getData('main_opt_id') ? $this->getData('main_opt_id') . $this->getData('ajax_load') : $this->getData();
            $cacheKeyInfo = [
                'PRODUCT_FILTER_WIDGET',
                $this->_storeManager->getStore()->getId(),
                $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
                $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
                intval($this->getRequest()->getParam(self::PAGE_VAR_NAME, 1)),
                $data
            ];
            $this->setData('cache_key_info', [md5(json_encode($cacheKeyInfo))]);
        }
        return $this->getData('cache_key_info');
    }

    public function getAddToCartUrl($product, $additional = [])
    {
        return $this->_cartHelper->getAddUrl($product, $additional);
    }
    
    public function getProductListBlock()
    {
        return $this->getObjectManager()->get('\Magento\Catalog\Block\Product\ListProduct');
    }
    
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product): array
    {
        $url =  $this->getListProduct()->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->getUrlHelper()->getEncodedUrl($url),
            ]
        ];
    }
    
        
    protected function _getBestSellingCollection()
    {
        $orderItemCol = $this->getObjectManager()->get('Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory')->create()
            ->addFieldToSelect(['product_id'])
            ->addFieldToFilter('parent_item_id', array('null' => true));
        $orderItemCol->getSelect()
            ->columns(array('ordered_qty' => 'SUM(`main_table`.`qty_ordered`)'))
            ->group('main_table.product_id')
            ->joinInner(
                array('sfo' => $orderItemCol->getTable('sales_order')),
                "(main_table.order_id = sfo.entity_id) AND (sfo.state <> 'canceled')",
                []
            );
        $collection = $this->_getAllProductProductCollection();
        $collection->getSelect()
            ->joinLeft(
                array('sfoi' => $orderItemCol->getSelect()),
                'e.entity_id = sfoi.product_id',
                array('ordered_qty' => 'sfoi.ordered_qty')
            )
            ->where('sfoi.ordered_qty > 0')
            ->order('ordered_qty desc');
        return $collection;
    }
    
    protected function _getNewCollection()
    {
        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_getAllProductProductCollection();        
        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter(
            'news_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'news_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            [
                ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort(
            'news_from_date',
            'desc'
        );
        return $collection;
    }
    
    protected function _getMostViewedCollection()
    {
        $mvCollection = $this->getObjectManager()->get(\Magento\Reports\Model\ResourceModel\Product\CollectionFactory::class)
            ->create()
            ->addViewsCount()->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());        
        $this->setData('order_by', false);
        $this->setData('product_ids', false);
        $this->setData('conditions_encoded', false);
        
        $collection = $this->_getAllProductProductCollection();
        $collection->getSelect()
            ->joinRight(
                ['mv' => $mvCollection->getSelect()],
                'e.entity_id = mv.entity_id',
                ['views' => 'mv.views']
            )->order('views DESC');
        $collection->setPageSize($this->getPageSize());
        return $collection;
    }
    
    protected function _getLastXDaysMostViewedCollection(int $day = 30)
    {
        $today = time();
        $last = $today - (60*60*24*$day);
        $from = $this->_localeDate->date($last)->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $to = $this->_localeDate->date($today)->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        
        $mvCollection = $this->getObjectManager()->get(\Magento\Reports\Model\ResourceModel\Product\CollectionFactory::class)
            ->create()->addViewsCount($from, $to)
            ->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $this->setData('order_by', false);
        $this->setData('product_ids', false);
        $this->setData('conditions_encoded', false);
        
        $collection = $this->_getAllProductProductCollection();
        $collection->getSelect()
            ->joinRight(
                ['mv' => $mvCollection->getSelect()],
                'e.entity_id = mv.entity_id',
                ['views' => 'mv.views']
            )->order('views DESC');
        $collection->setPageSize($this->getPageSize());       
        return $collection;
    }
    
    protected function _getAllProductProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam(self::PAGE_VAR_NAME, 1))
            ->setFlag('NO_SORT', true);

        if ($productIds = $this->getData('product_ids')) {
            if (!is_array($productIds)) {
                $productIds = explode(',', $productIds);
            }
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $productIds = implode(',', $productIds);
            $collection->getSelect()->order(new \Zend_Db_Expr("FIELD(e.entity_id, $productIds)"));
        }
        if ($this->getData('conditions_encoded')) {
            $conditions = $this->getConditions();
            $conditions->collectValidatedAttributes($collection);
            $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
        }
        return $collection;
    }

    public function createCollection()
    {
        if (!($this->getData('ajax_load'))) {
            $displayType = $this->getDisplayType();
            switch ($displayType) {
                case 'all_products':
                    $collection = $this->_getAllProductProductCollection();
                    break;
                case 'bestseller_products':
                    $collection = $this->_getBestSellingCollection();
                    break;
                case 'new_products':
                    $collection = $this->_getNewCollection();
                    break;
                case 'most_viewed_products':
                    $collection = $this->_getMostViewedCollection();
                    break;
                case 'last_month_most_viewed_products':
                    $collection = $this->_getLastXDaysMostViewedCollection();
                    break;
            }
            if ($this->getData('order_by')) {
                $sort = explode(' ', $this->getData('order_by'));
                $collection->addAttributeToSort($sort[0], $sort[1]);
            }
            $this->_eventManager->dispatch(
                'catalog_block_product_list_collection',
                ['collection' => $collection]
            );
            if (class_exists('\Codazon\ThemeLayoutPro\Helper\Data')) {
                $this->productLazyImg = $this->getThemeHelper()->enableProductLazyImg();
            }
            if ((bool)$this->getData('disable_lazyimg') === true) {
                $this->productLazyImg = false;
            }
            return $collection;
        } else {
            return $this->productCollectionFactory->create();
        }
    }
    
    public function getThemeHelper()
    {
        if ($this->themeHelper === null) {
            $this->themeHelper = $this->getObjectManager()->get(\Codazon\ThemeLayoutPro\Helper\Data::class);
        }
        return $this->themeHelper;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Catalog\Model\Product::CACHE_TAG];
    }

    /* public function subString($str, $strLenght)
    {
        $str = $this->stripTags($str);
        if(strlen($str) > $strLenght) {
            $strCutTitle = substr($str, 0, $strLenght);
            $str = substr($strCutTitle, 0, strrpos($strCutTitle, ' '))."&hellip;";
        }
        return $str;
    } */
    public function subString($str, $strLenght)
	{
		$str = strip_tags($str);
        if(mb_strlen($str) > $strLenght) {
            $strCutTitle = mb_substr($str, 0, $strLenght);
            $str = mb_substr($strCutTitle, 0, mb_strrpos($strCutTitle, ' '))."&hellip;";
        }
        return $str;
	}

    public function getTemplate()
    {
        $displayType = $this->getData('display_type');
        if ($displayType == 'recently_viewed_products') {
            return 'Codazon_ProductFilter::product/recently-viewed.phtml';
        } else {
            $isAjax = !($this->getData('ajax_load'));
            if ($isAjax){
                $template = $this->getData('filter_template');
                if ($template == 'custom') {
                    return $this->getData('custom_template');
                } else {
                    return $template;
                }
            } else {
                if ($this->getData('main_opt_id')) {
                    return 'Codazon_ThemeLayoutPro::widget/main-block-widget.phtml';
                } else {
                    return 'Codazon_ProductFilter::ajax/first_load.phtml';
                }
            }
        }
    }
    
    public function getElementShow()
    {
        if (!$this->_show) {
            $this->_show = explode(',', $this->getData('show'));
        }
        return $this->_show;
    }
    
    public function isShow($item)
    {
    	return in_array($item, $this->getElementShow());
    }
    
    public function getBlockId()
    {
    	return uniqid("cdz_block_");
    }
    
    public function getFilterData()
    {
        if (!$this->_filterData) {
            $this->_filterData = [
                'is_ajax'               =>  1,
                'title'                 =>  $this->getData('title'),
                'display_type'          =>  $this->getData('display_type'),
                'products_count'        =>  $this->getData('products_count'),
                'order_by'              =>  $this->getData('order_by'),
                'show'                  =>  $this->getData('show'),
                'thumb_width'           =>  $this->getData('thumb_width'),
                'thumb_height'          =>  $this->getData('thumb_height'),
                'filter_template'       =>  $this->getData('filter_template'),
                'custom_template'       =>  $this->getData('custom_template'),
                'show_slider'           =>  $this->getData('show_slider'),
                'conditions_encoded'    =>  $this->getData('conditions_encoded'),
                'slider_nav'            => (int)$this->getData('slider_nav'),
                'slider_dots'           => (int)$this->getData('slider_dots'),
                'slider_autoplay'       => (int)$this->getData('slider_autoplay'),
                'slider_autoplay_timeout' => $this->getData('slider_autoplay_timeout'),
                'total_cols'            => (int)$this->getData('total_cols'),
                'total_rows'            => (int)$this->getData('total_rows'),
                'slider_margin'         => (int)$this->getData('slider_margin'),
                'cache_lifetime'        => $this->getData('cache_lifetime'),
                'product_ids'           => $this->getData('product_ids'),
                'disable_lazyimg'       => (int)$this->getData('disable_lazyimg'),
                'name_substr_length'    => (int)$this->getData('name_substr_length'),
                'cache_key_info'        => $this->getCacheKeyInfo(),
            ];
            $adapts = array('1900', '1600', '1420', '1280','980','768','480','320','0');
            foreach ($adapts as $adapt) {
                $this->_filterData['items_' . $adapt] = (float)$this->getData('items_' . $adapt);
            }
        }
        return $this->_filterData;
    }
    
    public function getSliderData() {
        if (!$this->_sliderData) {
            $this->_sliderData = [
                'nav'       => (bool)$this->getData('slider_nav'),
                'dots'      => (bool)$this->getData('slider_dots'),
                'autoplay'  => (bool)$this->getData('slider_autoplay'),
                'autoplayTimeout' => $this->getData('slider_autoplay_timeout') ? : 5000
            ];
            $adapts = array('1900', '1600', '1420', '1280','980','768','480','320','0');
            foreach ($adapts as $adapt) {
                 $this->_sliderData['responsive'][$adapt] = ['items' => (float)$this->getData('items_' . $adapt)];
            }
            $this->_sliderData['margin'] = (float)$this->getData('slider_margin');
        }
        return $this->_sliderData;
    }
    
    public function getGridData() {
        $adapts = array('1900', '1600', '1420', '1280','980','768','480','320','0');
        $itemPerRow = [];
        foreach ($adapts as $adapt) {
            $itemPerRow[$adapt] = (float)$this->getData('items_' . $adapt);
        }
        return $itemPerRow;
    }
    
    public function getProductDefaultQty($product)
    {
        $qty = $this->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }
    
    public function getQuantityValidators()
    {
        $validators = [];
        $validators['required-number'] = true;
        return $validators;
    }
    
    public function getProductDetailsHtml(\Magento\Catalog\Model\Product $product)
    {
        $renderer = $this->getDetailsRenderer($product->getTypeId());
        if ($renderer) {
            if (get_class($renderer) == 'Magento\Swatches\Block\Product\Renderer\Listing\Configurable\Interceptor') {
                return '';
            }
            $renderer->setProduct($product);
            return $renderer->toHtml();
        }
        return '';
    }
    
    public function getSwatchesBlock()
    {
        return $this->getLayout()->createBlock('Magento\Swatches\Block\Product\Renderer\Listing\Configurable')
            ->setTemplate('Codazon_ProductFilter::swatches/renderer.phtml')
            ->setData(['thumb_width' => $this->getData('thumb_width'), 'thumb_height' => $this->getData('thumb_height')]);
    }
    
    public function getImageHtml($product, $width, $height, $imageHelper)
    {
        if ($this->productLazyImg) {
            $mainImage = $imageHelper->init($product, 'category_page_grid')->setImageFile($product->getData('small_image'));
            $mainImage = '<img data-hasoptions=\''.($product->getHasOptions()? '1':'0').'\' class="product-image-photo main-img" data-lazysrc="'.$mainImage->resize($width, $height)->getUrl().'" alt="'. ($label = $this->escapeHtmlAttr($mainImage->getLabel())) .'" />';
            $hoveredImage = $imageHelper->init($product, 'category_page_grid')->setImageFile($product->getData('thumbnail'));
            $hoveredImage = '<img class="product-image-photo hovered-img" data-lazysrc="'.$hoveredImage->resize($width, $height)->getUrl().'" alt="'.$label.'" />';
        } else {
            $mainImage = $imageHelper->init($product, 'category_page_grid')->setImageFile($product->getData('small_image'));
            $mainImage = '<img data-hasoptions=\''.($product->getHasOptions()? '1':'0').'\' class="product-image-photo main-img" src="'.$mainImage->resize($width, $height)->getUrl().'" alt="'. ($label = $this->escapeHtmlAttr($mainImage->getLabel())) .'" />';
            $hoveredImage = $imageHelper->init($product, 'category_page_grid')->setImageFile($product->getData('thumbnail'));
            $hoveredImage = '<img class="product-image-photo hovered-img" src="'.$hoveredImage->resize($width, $height)->getUrl().'" alt="'.$label.'" />';
        }
        return $mainImage . $hoveredImage;
    }
}
