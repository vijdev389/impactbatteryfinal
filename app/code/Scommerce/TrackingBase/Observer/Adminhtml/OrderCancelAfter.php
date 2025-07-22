<?php

namespace Scommerce\TrackingBase\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Scommerce\TrackingBase\Helper\Data;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductId;
use Scommerce\TrackingBase\Model\GetProductPrice;
use Scommerce\TrackingBase\Model\GetProductVariant;

class OrderCancelAfter implements ObserverInterface
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
     * @var GetProductId
     */
    protected $_getProductId;

    /**
     * @var GetBrand
     */
    protected $_getBrand;

    /**
     * @var
     */
    protected $_getProductCategory;

    /**
     * @var GetProductPrice
     */
    protected $_getProductPrice;

    /**
     * @var GetProductVariant
     */
    protected $_getProductVariant;

    public function __construct(
        SessionManagerInterface $coreSession,
        Data $helper,
        GetProductId $getProductId,
        GetBrand $getBrand,
        GetProductCategory $getProductCategory,
        GetProductPrice $getProductPrice,
        GetProductVariant $getProductVariant
    ) {
        $this->_coreSession = $coreSession;
        $this->_helper = $helper;
        $this->_getProductId = $getProductId;
        $this->_getBrand = $getBrand;
        $this->_getProductCategory = $getProductCategory;
        $this->_getProductPrice = $getProductPrice;
        $this->_getProductVariant = $getProductVariant;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId= $order->getStoreId();
        if ($this->_helper->isEnabled($storeId)
            && $this->_helper->getSendRefundOnOrderCancellation($storeId)
            && $this->_helper->isEnhancedEcommerceEnabled($storeId)
        ) {
            $orderId = $order->getIncrementId();
            $products = $this->getOrderItems($order);

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
            $response = [
                'orderId'   => $orderId,
                'storeId'   => $storeId,
                'products'  => $products,
                'fullRefund'=> true,
                'value'     => $orderGrandTotal,
                'currency'  => $this->_helper->sendBaseData() ? $order->getBaseCurrencyCode() : $order->getOrderCurrencyCode(),
                'tax'       => $orderTax,
                'shipping'  => $orderShippingTotal,
                'affiliation' => $this->_helper->getAffiliation($order->getStoreId()),
                'coupon' => $order->getCouponCode()
            ];

            $this->_coreSession->setRefundOrder(json_encode($response));
        }
    }

    private function getOrderItems($order)
    {
        $products = [];
        $refundCurrency = $order->getBaseCurrencyCode();
        foreach ($order->getAllVisibleItems() as $item) {
            $sku = $this->_helper->sendParentSKU() && $item->getParentItemId() ?
                $this->_getProductId->execute($item->getParentItem()) :
                $item->getProductId();
            $prod = $item->getProduct();
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
                'id' => $sku,
                'qty' => $item->getQty(),
                'name' => trim($item->getName()),
                'brand' => $this->_getBrand->execute($prod),
                'category' => $this->_getProductCategory->execute($prod),
                'price' => $price,
                'quantity' => $item->getQtyOrdered(),
                'variant' => $this->_getProductVariant->execute($prod, $item)
            ];

        }
        return $products;
    }
}
