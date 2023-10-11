<?php

namespace Scommerce\TrackingBase\Plugins;

class ProductListPlugin
{
    protected $slidersTracker;

    public function __construct(
        \Scommerce\TrackingBase\Model\SlidersTracker $slidersTracker
    ) {
        $this->slidersTracker = $slidersTracker;
    }

    public function afterCreateCollection(\Magento\CatalogWidget\Block\Product\ProductsList $subject, $result) 
    {
        $this->slidersTracker->setSlidersCollection($result->getItems());
        return $result;
    }
}
