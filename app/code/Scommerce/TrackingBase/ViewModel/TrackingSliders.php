<?php

namespace Scommerce\TrackingBase\ViewModel;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Scommerce\TrackingBase\Model\GetProductId;

class TrackingSliders extends DataObject implements ArgumentInterface
{
    protected $slidersTracker;

    protected $helper;

    protected $getBrand;

    protected $getProductPrice;

    protected $getProductCategory;

    protected $getProductId;

    protected $productRepository;

    public function __construct(
        \Scommerce\TrackingBase\Model\SlidersTracker $slidersTracker,
        \Scommerce\TrackingBase\Helper\Data $helper,
        \Scommerce\TrackingBase\Model\GetBrand $getBrand,
        \Scommerce\TrackingBase\Model\GetProductPrice $getProductPrice,
        \Scommerce\TrackingBase\Model\GetProductCategory $getProductCategory,
        GetProductId $getProductId,
        ProductRepository $productRepository
    ) {
        $this->slidersTracker = $slidersTracker;
        $this->helper = $helper;
        $this->getBrand = $getBrand;
        $this->getProductPrice = $getProductPrice;
        $this->getProductCategory = $getProductCategory;
        $this->productRepository = $productRepository;
        $this->getProductId = $getProductId;
    }

    public function getNonce()
    {
        return $this->helper->getNonce();
    }

    public function getSlidersCollection()
    {
        if ($products = $this->slidersTracker->getSlidersCollection()) {
            $_loop = 1;
            foreach ($products as $product) {
                $loadedProduct = $this->productRepository->get($product->getSku());
                $id = $this->getProductId->execute($loadedProduct);
                $_products[] = [
                    'id' => $id,
                    'name' => $this->helper->escapeJsQuote(trim($loadedProduct->getName())),
                    'category' => $this->getProductCategory->execute($loadedProduct),
                    'brand' => $this->helper->escapeJsQuote($this->getBrand->execute($loadedProduct)),
                    'list' => $this->helper->escapeJsQuote($this->helper->getSliderText()),
                    'price' => $this->getProductPrice->execute($loadedProduct),
                    'url' => $loadedProduct->getProductUrl(),
                    'position' => $_loop
                ];
                $_loop++;
            }
            return $_products;
        }
        return false;
    }
}
