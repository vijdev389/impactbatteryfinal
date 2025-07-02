<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GoogleTagManagerPro\Model\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Scommerce\GoogleTagManagerPro\Helper\Data;
use Scommerce\GoogleTagManagerPro\Model\Session as GtmSession;

class CheckoutCartAddAfter implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Http
     */
    protected $_request;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var GtmSession
     */
    protected $_gtmSession;


    protected $_productRepository;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param SessionManagerInterface $coreSession
     * @param Http $request
     * @param Data $helper
     * @param Registry $coreRegistry
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        SessionManagerInterface $coreSession,
        Http $request,
        Data $helper,
        Registry $coreRegistry,
        GtmSession $gtmSession,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreSession = $coreSession;
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_gtmSession = $gtmSession;
        $this->_productRepository = $productRepository;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isEnhancedEcommerceEnabled() && $this->_helper->isEnabled()){
            $productId = $this->_request->getParam('product', 0);
            $qty = $this->_request->getParam('qty', 1);
            if ($productId==0){
                $itemId = $this->_request->getParam('item', 0);
                if ($itemId>0){
                    $wishList = $this->_objectManager->create('\Magento\Wishlist\Model\Wishlist')->load($itemId);
                    if ($wishList){
                        $productId = $wishList->getProductId();
                    }
                }
            }

            $group = $this->_request->getParam('super_group');

            $quoteItem = $observer->getEvent()->getQuoteItem();
            $product = null;
            if ($quoteItem->getHasChildren() && $quoteItem->getProductType() == 'configurable') {
                $children = $quoteItem->getChildren();
                if (is_array($children) && count($children)) {
                    $qi = $quoteItem->getChildren()[0];
                    $product = $this->_objectManager->create('\Magento\Catalog\Model\Product')
                        ->load($qi->getProductId());
                }
            }

            if ($product == null) {
                $product = $this->_objectManager->create('\Magento\Catalog\Model\Product')
                    ->load($productId);
            }

            if (!$product->getId()) {
                return;
            }

            $brand = $this->_helper->getBrand($product);
            $allSkus = [$product->getSku()];
            if ($quoteItem) {
                $sku = $this->_helper->getParentSKU($quoteItem);
                $allSkus = array_merge($allSkus, [$quoteItem->getProduct()->getData('sku')]);
            } else {
                $sku = $product->getSku();
            }
            $list = $this->_request->getParam('sc_list');
            if (!$list) {
                $list = $this->_gtmSession->getImpressionForProduct($sku);
            }
            if (!$list) {
                $list = $this->_gtmSession->getImpressionForProduct($observer->getEvent()->getProduct()->getData('sku'), true);
            }
            $this->setTrackingData($quoteItem, 'list', $list);

            $cat = $this->_helper->getProductCategoryName($product);
            $this->setGoogleCategory($quoteItem, $cat);

            if ($quoteItem->getProductType() == 'bundle') {
                $price = $quoteItem->getPrice();
            } else {
                $price = $product->getFinalPrice();
            }
            if ($price == 0) {
                $price = $product->getPriceInfo()->getPrice('final_price')->getValue();
            }
            $price = number_format($price, 2);
            if (!$quoteItem->getPriceInclTax()) {
                $quoteItem->setData('sc_need_price_update', true);
            } else {
                $price = $quoteItem->getPriceInclTax();
            }

            $productToBasket = array(
                'id' => $sku,
                'name' => $product->getName(),
                'category' => $cat,
                'brand' => $brand,
                'price' => $price,
                'qty'=> $qty,
                'currency' => $this->_helper->getCurrencyCode(),
                'list' => $list,
                'allSkus' => $allSkus
            );
            $productToBasket = [$productToBasket];
            if ($group) {
                $additionalItems = $this->getGroupedItems($group, $quoteItem, $list, $product->getSku());
                if ($additionalItems) {
                    $productToBasket = array_merge($productToBasket, $additionalItems);
                }
            }

            $this->_coreSession->setProductToBasket(json_encode($productToBasket));
        }
    }

    protected function setTrackingData($quoteItem, $key, $value)
    {
        $trackingData = $quoteItem->getScTrackingData();
        if ($trackingData) {
            $trackingData = json_decode($trackingData, true);
        } else {
            $trackingData = [];
        }
        $trackingData[$key] = $value;
        $trackingData = json_encode($trackingData);
        $quoteItem->setScTrackingData($trackingData);
        if ($quoteItem->getHasChildren()) {
            $children = $quoteItem->getChildren();
            foreach ($children as $child) {
                $child->setScTrackingData($trackingData);
            }
        }
    }

    /**
     * @param $quoteItem
     * @param $category
     */
    protected function setGoogleCategory($quoteItem, $category)
    {
        $quoteItem->setGoogleCategory($category);
        if ($quoteItem->getHasChildren()) {
            $children = $quoteItem->getChildren();
            foreach ($children as $child) {
                $child->setGoogleCategory($category);
            }
        }
    }

    /**
     * @param $product
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCategory($product)
    {
        $primaryCategory = $product->getPrimaryCategory();
        if ($primaryCategory) {
            $cat = $this->_helper->getCategory($primaryCategory);
            if ($cat != null) {
                return $this->_helper->getCategoryPath($cat);
            }
        }
        $category = $this->_gtmSession->getCategoryForProduct($product->getId());
        if ($category) {
            return $category;
        }
        $currentCategory = $this->_coreRegistry->registry("current_category");
        if ($currentCategory) {
            return $this->_helper->getCategoryPath($currentCategory);
        }
        return $this->_helper->getProductCategoryName($product);
    }

    /**
     * @param $group
     * @param $quoteItem
     * @param $list
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getGroupedItems($group, $quoteItem, $list, $mainSku)
    {
        $products = [];
        foreach ($group as $id => $qty) {
            if ($id == $quoteItem->getProductId() || !$qty) continue;

            $_pr = $this->_productRepository->getById($id);
            $cat = $this->_helper->getProductCategoryName($_pr);
            $brand = $this->_helper->getBrand($_pr);
            $products[] = [
                'id' => $_pr->getSku(),
                'name' => $_pr->getName(),
                'category' => $cat,
                'brand' => $brand,
                'price' => $this->_helper->productPrice($_pr),
                'qty'=> $qty,
                'currency' => $this->_helper->getCurrencyCode(),
                'list' => $list,
                'allSkus' => [$_pr->getSku(), $mainSku]
            ];
        }
        return $products;
    }
}
