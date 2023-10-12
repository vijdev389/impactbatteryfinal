<?php

namespace Scommerce\TrackingBase\ViewModel;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class TrackingSliders extends DataObject implements ArgumentInterface
{
    protected $slidersTracker;

    protected $helper;

    protected $getBrand;

    protected $getProductPrice;

    protected $getProductCategory;

    public function __construct(
        \Scommerce\TrackingBase\Model\SlidersTracker $slidersTracker,
        \Scommerce\TrackingBase\Helper\Data $helper,
        \Scommerce\TrackingBase\Model\GetBrand $getBrand,
        \Scommerce\TrackingBase\Model\GetProductPrice $getProductPrice,
        \Scommerce\TrackingBase\Model\GetProductCategory $getProductCategory
    ) {
        $this->slidersTracker = $slidersTracker;
        $this->helper = $helper;
        $this->getBrand = $getBrand;
        $this->getProductPrice = $getProductPrice;
        $this->getProductCategory = $getProductCategory;
    }

    public function getSlidersCollection() 
    {
        if ($products = $this->slidersTracker->getSlidersCollection()) {
            $_loop = 1;
            foreach ($products as $product) {
                $_products[] = [
                    'id' => $this->helper->escapeJsQuote($product->getId()),
                    'name' => $this->helper->escapeJsQuote(trim($product->getName())),
                    'category' => $this->getProductCategory->execute($product),
                    'brand' => $this->helper->escapeJsQuote($this->getBrand->execute($product)),
                    'list' => $this->helper->escapeJsQuote($this->helper->getSliderText()),
                    'price' => $this->getProductPrice->execute($product),
                    'url' => $product->getProductUrl(),
                    'position' => $_loop
                ];
                $_loop++;
            }
            return $_products;
        }
        return false;
    }
}
