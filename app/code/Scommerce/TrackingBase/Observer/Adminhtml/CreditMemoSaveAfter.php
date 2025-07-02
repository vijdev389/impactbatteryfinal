<?php
/**
 * Copyright © 2021 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\TrackingBase\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Scommerce\TrackingBase\Helper\Data;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductId;
use Scommerce\TrackingBase\Model\GetProductPrice;
use Scommerce\TrackingBase\Model\GetProductVariant;

/**
 * Class CreditMemoSaveAfter
 * @package Scommerce\TrackingBase\Observer\Adminhtml
 */
class CreditMemoSaveAfter implements ObserverInterface
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

    /**
     * @param SessionManagerInterface $coreSession
     * @param Data $helper
     * @param GetProductId $getProductId
     */
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
        /** @var CreditmemoInterface $creditMemo */
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order = $creditMemo->getOrder();
        $storeId= $order->getStoreId();
        if ($this->_helper->isEnabled($storeId)
            && $this->_helper->isEnhancedEcommerceEnabled($storeId)
            && $this->_helper->getSendAdminOrdersEnabled($storeId)
        ) {
            $orderId = $order->getIncrementId();
            $products = array();
            $fullRefund = false;

            $cmCount = 0;
            foreach ($creditMemo->getAllItems() as $item) {
                $cmCount += $item->getQty();
            }

            $oCount = 0;
            foreach ($order->getAllItems() as $item) {
                $oCount += $item->getQtyOrdered();
            }

            if ($oCount == $cmCount) {
                $fullRefund = true;
            }

            if ($fullRefund == false) {
                foreach ($creditMemo->getAllItems() as $item) {
                    if($item->getBasePrice() <= 0) continue;
                    $sku = $this->_helper->sendParentSKU() && $item->getParentItemId() ?
                        $this->_getProductId->execute($item->getParentItem()) :
                        $item->getProductId();
                    $prod = $item->getOrderItem()->getProduct();
                    $products[] = [
                        'id' => $sku,
                        'qty' => $item->getQty(),
                        'name' => trim($item->getName()),
                        'brand' => $this->_getBrand->execute($prod),
                        'category' => $this->_getProductCategory->execute($prod),
                        'price' => $this->_getProductPrice->execute($prod),
                        'quantity' => $item->getQty(),
                        'variant' => $this->_getProductVariant->execute($prod, $item)
                    ];
                }
            }

            $response = [
                'orderId'   => $orderId,
                'storeId'   => $storeId,
                'products'  => $products,
                'fullRefund'=> $fullRefund,
                'value'     => $this->_helper->sendBaseData() ? $creditMemo->getBaseGrandTotal() : $creditMemo->getGrandTotal(),
                'currency'  => $creditMemo->getOrderCurrencyCode(),
                'tax'       => $this->_helper->sendBaseData() ? $creditMemo->getBaseTaxAmount() : $creditMemo->getTaxAmount(),
                'shipping'  => $this->_helper->sendBaseData() ? $creditMemo->getBaseShippingAmount() : $creditMemo->getShippingAmount(),
                'affiliation' => $this->_helper->getAffiliation($creditMemo->getStoreId()),
                'coupon' => $order->getCouponCode()
            ];

			$this->_coreSession->setRefundOrder(json_encode($response));
        }
    }
}
