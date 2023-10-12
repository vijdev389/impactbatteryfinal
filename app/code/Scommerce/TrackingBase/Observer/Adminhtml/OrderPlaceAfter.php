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
                $orderCurrency = $order->getBaseCurrencyCode();
                $orderGrandTotal = $this->_helper->isOrderTotalIncludedVAT() ? $order->getBaseGrandTotal() : $order->getBaseGrandTotal() - $order->getBaseTaxAmount();
                $orderShippingTotal = $order->getBaseShippingAmount();
                $orderTax = $order->getBaseTaxAmount();
            } else {
                $orderCurrency = $order->getOrderCurrencyCode();
                $orderGrandTotal = $this->_helper->isOrderTotalIncludedVAT() ? $order->getGrandTotal() : $order->getGrandTotal() - $order->getTaxAmount();
                $orderShippingTotal = $order->getShippingAmount();
                $orderTax = $order->getTaxAmount();
            }

            foreach ($order->getAllItems() as $item) {
                /** @var $item Item */
                if ($item->getParentItem()) continue;
                $products[] = [
                    'name'	=> trim($item->getName()),
                    'id' 	=> $this->_helper->escapeJsQuote(
                        $this->_getProductId->execute($item)
                    ),
                    'price'	=> $this->_getProductPrice->execute($item),
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
                'currency'      => $orderCurrency,
                'products'  	=> $products,
            ];

            $this->_coreSession->setOrderData(json_encode($response));
        }
    }
}
