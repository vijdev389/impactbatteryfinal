<?php
/**
 * Copyright © 2021 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\TrackingBase\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Scommerce\TrackingBase\Helper\Data;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductId;
use Scommerce\TrackingBase\Model\GetProductPrice;

class WishlistAddProductObserver implements ObserverInterface
{
    private $helper;

    private $sessionManager;

    private $getBrand;

    private $getProductCategory;

    private $getProductPrice;

    private $getProductId;

    /**
     * @param Data $helper
     * @param SessionManagerInterface $sessionManager
     * @param GetBrand $getBrand
     * @param GetProductCategory $getProductCategory
     * @param GetProductPrice $getProductPrice
     * @param GetProductId $getProductId
     */
    public function __construct(
        Data $helper,
        SessionManagerInterface $sessionManager,
        GetBrand $getBrand,
        GetProductCategory $getProductCategory,
        GetProductPrice $getProductPrice,
        GetProductId $getProductId
    ) {
        $this->helper = $helper;
        $this->sessionManager = $sessionManager;
        $this->getBrand = $getBrand;
        $this->getProductCategory = $getProductCategory;
        $this->getProductPrice = $getProductPrice;
        $this->getProductId = $getProductId;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }
        $wishlist = $observer->getEvent()->getWishlist();
        $product = $observer->getEvent()->getProduct();
        $item =  $observer->getEvent()->getItem();

        $cat = $this->getProductCategory->execute($product);
        $brand = $this->getBrand->execute($product);
        $id = $this->getProductId->execute($product);
        $price = $this->getProductPrice->execute($product);
        $data = [
            'value' => $price,
            'item' => [
                'id' => $id,
                'name' => trim($product->getName()),
                'category' => $cat,
                'brand' => $brand,
                'price' => $price,
                'quantity'=> $item->getQty(),
                'allSkus' => [$id],
                '_realProductId' => $product->getId(),
                'variant' => ''
            ]
        ];

        $this->sessionManager->setProductToWishlist(json_encode($data));
    }
}
