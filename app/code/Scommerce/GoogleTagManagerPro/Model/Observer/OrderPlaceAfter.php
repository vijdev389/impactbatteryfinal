<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GoogleTagManagerPro\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Scommerce\GoogleTagManagerPro\Helper\Data;
use Scommerce\GoogleTagManagerPro\Model\Session;

/**
 * Class OrderPlaceAfter
 * @package Scommerce\GoogleTagManagerPro\Model\Observer
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
     * @var Session
     */
	protected $_gtmSession;

    /**
     * @param SessionManagerInterface $coresession
     * @param Data $helper
     */
    public function __construct(
        SessionManagerInterface $coresession,
        Data $helper,
        Session $gtmSession
    ) {
        $this->_coreSession = $coresession;
        $this->_helper = $helper;
        $this->_gtmSession = $gtmSession;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        if ($this->_helper->isEnhancedEcommerceEnabled($storeId)
            && $this->_helper->isEnabled($storeId)
            && $this->_helper->getSendAdminOrdersEnabled($storeId)
        ) {
            $products = array();

            if ($this->_helper->sendBaseData()):
                $orderCurrency 		= $order->getBaseCurrencyCode();
                $orderGrandTotal 	= $this->_helper->isOrderTotalIncludedVAT() ? $order->getBaseGrandTotal() : $order->getBaseGrandTotal() - $order->getBaseTaxAmount();
                $orderShippingTotal	= $order->getBaseShippingAmount();
                $orderTax			= $order->getBaseTaxAmount();
            else:
                $orderCurrency 		= $order->getOrderCurrencyCode();
                $orderGrandTotal 	= $this->_helper->isOrderTotalIncludedVAT() ? $order->getGrandTotal() : $order->getGrandTotal() - $order->getTaxAmount();
                $orderShippingTotal	= $order->getShippingAmount();
                $orderTax			= $order->getTaxAmount();
            endif;

            foreach ($order->getAllItems() as $item) {
                /** @var $item \Magento\Sales\Model\Order\Item */
                if ($item->getParentItemId()) continue;
                $products[] = array(
                    'name'	=> $item->getName(),
                    'id' 	=> $this->_helper->getParentSKU($item),
                    'price'	=> ($this->_helper->sendBaseData() == true ? $item->getBasePrice() : $item->getPrice()),
                    'brand' => $this->_helper->getBrand($item->getProduct()),
                    'category' => $this->_helper->getProductCategoryName($item->getProduct()),
                    'quantity' 	=> $item->getQtyOrdered()
                );
            }

            $this->_gtmSession->clearTrackingData();

            $response = array(
                'id'   			=> $order->getIncrementId(),
                'affiliation'   => $order->getAffiliation(),
                'revenue'   	=> $orderGrandTotal,
                'tax'   		=> $orderTax,
                'shipping'   	=> $orderShippingTotal,
                'coupon'   		=> $order->getCouponCode(),
                'storeId'   	=> $storeId,
                'currency'      => $orderCurrency,
                'products'  	=> $products,
            );

            $this->_coreSession->setOrderData(json_encode($response));
        }
    }

}
