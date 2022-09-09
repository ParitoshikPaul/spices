<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogRule data helper
 */
namespace Codazon\ProductLabel\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_saleRuleModel;
    protected $_labelModel;
    protected $_labels = null;
    protected $_storeManager;
    protected $_labelBlock;
    
    protected $_renderedLabels = [];
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\SalesRule\Model\Rule $saleRuleModel,
        \Codazon\ProductLabel\Model\ProductLabel $labelModel,
        \Codazon\ProductLabel\Block\ProductLabel $labelBlock,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_saleRuleModel = $saleRuleModel;
        $this->_labelModel = $labelModel;
        $this->_storeManager = $storeManager;
        $this->_labelBlock = $labelBlock;
    }
    public function getLabels(){
        if ($this->_labels === null) {
            $this->_labels = $this->_labelModel->getCollection()->setStoreId($this->_storeManager->getStore(true)->getId())
                ->addAttributeToFilter('is_active', ['eq' => 1])
                ->addAttributeToSelect(['content', 'label_image', 'label_background', 'custom_class', 'custom_css']);
        }
        return $this->_labels;
    }
    public function showLabel($_product){
        if (!isset($this->_renderedLabels[$_product->getId()])) {
            $labels = $this->getLabels();
            $validLabels = [];
            foreach ($labels as $label) {
                $conditionsArr = $label->getConditions();
                $this->_labelModel->getConditions()->setConditions([])->loadArray($conditionsArr);
                if ($validate = (bool)$this->_labelModel->validate($_product)) {
                    $validLabels[] = $label;
                } else {
                    if ($_product->getTypeId() == 'configurable') {
                        $_children = $_product->getTypeInstance()->getUsedProducts($_product);
                        if(count($_children) > 0){
                            foreach($_children as $_child){
                                if($validate = $this->_labelModel->validate($_child)){
                                    $validLabels[] = $label; break;
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($validLabels)) {
                $this->_renderedLabels[$_product->getId()] = $this->_labelBlock->setObject(['labels' => $validLabels, 'product' => $_product])->getHtml();
            } else {
                $this->_renderedLabels[$_product->getId()] = '';
            }
        }
        return $this->_renderedLabels[$_product->getId()];
    }
    
    public function calcPriceRule($actionOperator, $ruleAmount, $price)
    {
        $priceRule = 0;
        switch ($actionOperator) {
            case 'to_fixed':
                $priceRule = min($ruleAmount, $price);
                break;
            case 'to_percent':
                $priceRule = $price * $ruleAmount / 100;
                break;
            case 'by_fixed':
                $priceRule = max(0, $price - $ruleAmount);
                break;
            case 'by_percent':
                $priceRule = $price * (1 - $ruleAmount / 100);
                break;
        }
        return $priceRule;
    }
    
    public function getSavePercent($_product)
    {
        if ($_product->getTypeId() == 'configurable') {
			$_children = $_product->getTypeInstance()->getUsedProducts($_product);
            $result = [];
			if (count($_children) > 0) {
                foreach($_children as $_child) {
                    $orgPrice = (float)$_child->getPriceInfo()->getPrice('regular_price')->getValue();
                    $finalPrice = (float)$_child->getPriceInfo()->getPrice('final_price')->getValue();
					$result[] = ($orgPrice > 0) ? ($orgPrice - $finalPrice) / $orgPrice : 0;
				}                
				return number_format(100*(float)(max($result)), 0);
			} else {
				return 0;
			}
		}else{
            $orgPrice = (float)$_product->getPriceInfo()->getPrice('regular_price')->getValue();
            if (!$orgPrice) {
                return 0;
            } else {
                $finalPrice = (float)$_product->getPriceInfo()->getPrice('final_price')->getValue();
                return number_format(100*(float)(($orgPrice - $finalPrice) / $orgPrice), 0);
            }
		}
    }
    
    public function getSavePrice($_product)
    {
        if ($_product->getTypeId() == 'configurable') {
			$_children = $_product->getTypeInstance()->getUsedProducts($_product);
            $result = [];
			if (count($_children) > 0) {
                foreach($_children as $_child) {
                    $finalPrice = (float)$_child->getPriceInfo()->getPrice('final_price')->getValue();
					$result[] = $_child->getPrice() - $finalPrice;
				}
				return $this->_priceHelper->currency(max($result), true, false);
			} else {
				return $this->_priceHelper->currency(0, true, false);
			}
		}else{
            $finalPrice = (float)$_product->getPriceInfo()->getPrice('final_price')->getValue();
            return $this->_priceHelper->currency($_product->getPrice() - $finalPrice, true, false);
		}
    }
}
