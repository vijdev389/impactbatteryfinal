<?php
/**
 * Copyright © 2021 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\TrackingBase\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Sales\Model\Order\Item;
use Scommerce\TrackingBase\Helper\Data;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductId;
use Scommerce\TrackingBase\Model\GetProductPrice;
use Scommerce\TrackingBase\Model\GetProductVariant;

/**
 * Class OrderPlaceAfter
 * @package Scommerce\TrackingBase\Model\Observer\Adminhtml
 */
class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

	/**
     * @var SessionManagerInterface
     */
	protected $_coreSession;

    /**
     * @var GetBrand
     */
	protected $_getBrand;

    /**
     * @var GetProductCategory
     */
    protected $_getProductCategory;

    /**
     * @var GetProductId
     */
    protected $_getProductId;

    /**
     * @var GetProductPrice
     */
    protected $_getProductPrice;

    /**
     * @var GetProductVariant
     */
    protected $_getProductVariant;

    /**
     * OrderPlaceAfter constructor.
     * @param SessionManagerInterface $coreSession
     * @param Data $helper
     * @param GetBrand $getBrand
     * @param GetProductCategory $getProductCategory
     * @param GetProductId $getProductId
     * @param GetProductPrice $getProductPrice
     */
    public function __construct(
        SessionManagerInterface $coreSession,
        Data $helper,
        GetBrand $getBrand,
        GetProductCategory $getProductCategory,
        GetProductId $getProductId,
        GetProductPrice $getProductPrice,
        GetProductVariant $getProductVariant
    ) {
        $this->_coreSession = $coreSession;
        $this->_helper = $helper;
        $this->_getBrand = $getBrand;
        $this->_getProductCategory = $getProductCategory;
        $this->_getProductId = $getProductId;
        $this->_getProductPrice = $getProductPrice;
        $this->_getProductVariant = $getProductVariant;
    }

    /**
     * @param EventObserver $observer
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        if ($this->_helper->isEnhancedEcommerceEnabled($storeId)
            && $this->_helper->isEnabled($storeId)
            && $this->_helper->getSendAdminOrdersEnabled($storeId)
        ) {
            $products = array();

            if ($this->_helper->sendBaseData($storeId)) {
                $orderTax = $order->getBaseTaxAmount() + $order->getBaseDiscountTaxCompensationAmount();
                $orderShippingTotal = $order->getBaseShippingAmount() - $order->getBaseShippingDiscountAmount();
                $orderGrandTotal = $this->_helper->isOrderTotalIncludedVAT() ? $order->getBaseGrandTotal() : $order->getBaseGrandTotal() - $orderTax;
            } else {
                $orderTax = $order->getTaxAmount() + $order->getDiscountTaxCompensationAmount();
                $orderShippingTotal = $order->getShippingAmount() - $order->getShippingDiscountAmount();
                $orderGrandTotal = $this->_helper->isOrderTotalIncludedVAT() ? $order->getGrandTotal() : $order->getGrandTotal() - $orderTax;
            }

            $orderGrandTotal = $orderGrandTotal - $orderShippingTotal;
            foreach ($order->getAllItems() as $item) {
                /** @var $item Item */
                if ($item->getParentItem()) continue;
                if ($item->getDiscountAmount() <> 0) {
                    if ($this->_helper->sendBaseData()) {
                        $price = $item->getBasePrice() - $item->getBaseDiscountAmount() / $item->getQtyOrdered();
                    } else {
                        $price = $item->getPrice() - $item->getDiscountAmount() / $item->getQtyOrdered();
                    }
                } else {
                    $price = $this->_getProductPrice->executeBySku($item->getSku());
                    if ($price === false) {
                        $price = (int)$this->_getProductPrice->executeBySku($item->getData('sku'));
                    }
                }

                $products[] = [
                    'name'	=> trim($item->getName()),
                    'id' 	=> $this->_helper->escapeJsQuote(
                        $this->_getProductId->execute($item)
                    ),
                    'price'	=> $price,
                    'brand' => $this->_getBrand->execute($item->getProduct()),
                    'category' => $this->_getProductCategory->execute($item->getProduct()),
                    'quantity' 	=> $item->getQtyOrdered(),
                    'variant'   => $this->_getProductVariant->execute($item->getProduct(), $item)
                ];
            }

            $response = [
                'id'   			=> $order->getIncrementId(),
                'affiliation'   => $this->_helper->getAffiliation($order->getStoreId()),
                'revenue'   	=> $orderGrandTotal,
                'tax'   		=> $orderTax,
                'shipping'   	=> $orderShippingTotal,
                'coupon'   		=> $order->getCouponCode(),
                'storeId'   	=> $storeId,
                'currency'      => $this->_helper->sendBaseData() ? $order->getBaseCurrencyCode() : $order->getOrderCurrencyCode(),
                'products'  	=> $products,
            ];

            $this->_coreSession->setOrderData(json_encode($response));
        }
    }
}
