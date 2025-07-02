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

/**
 * Class CreditMemoSaveAfter
 * @package Scommerce\GoogleTagManagerPro\Model\Observer
 */
class CreditMemoSaveAfter implements ObserverInterface
{
    /**
     * @var \Scommerce\GoogleTagManagerPro\Helper\Data
     */
    protected $_helper;
	
	/**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
	protected $_coreSession;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $coresession
     * @param \Scommerce\GoogleTagManagerPro\Helper\Data $helper
     */
    public function __construct(
        SessionManagerInterface $coresession,
        Data $helper
    ) {
        $this->_coreSession = $coresession;
        $this->_helper = $helper;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order = $creditMemo->getOrder();
        $storeId= $order->getStoreId();
        if ($this->_helper->isEnabled($storeId)
            && $this->_helper->isEnhancedEcommerceEnabled($storeId)
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
            /*
            if (count($order->getAllItems())==count($creditMemo->getAllItems())){
                $fullRefund = true;
            }
            */

            if ($fullRefund==false){
                foreach ($creditMemo->getAllItems() as $item) {
                    if($item->getBasePrice()<=0) continue;
                    $sku = $this->_helper->getParentSKU($item);
                    $products[] = array('id' => $sku, 'qty' => $item->getQty());
                }
            }

            $response = array(
                'orderId'   => $orderId,
                'storeId'   => $storeId,
                'products'  => $products,
                'fullRefund'=> $fullRefund,
            );
			
			$this->_coreSession->setRefundOrder(json_encode($response));
        }
    }

}