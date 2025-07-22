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
use Scommerce\TrackingBase\Model\GetProductPrice;


class SalesQuoteItemSaveAfter implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var SessionManagerInterface
     */
    protected $_coreSession;

    protected $getProductPrice;

    /**
     * @param Data $helper
     * @param SessionManagerInterface $coreSession
     */
    public function __construct(
        Data $helper,
        SessionManagerInterface $coreSession,
        GetProductPrice $getProductPrice
    ) {
        $this->_helper = $helper;
        $this->_coreSession = $coreSession;
        $this->getProductPrice = $getProductPrice;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isEnhancedEcommerceEnabled() && $this->_helper->isEnabled()) {
            try {
                $products = $this->_coreSession->getProductToBasket();
                if (!$products) {
                    return;
                }

                $item = $observer->getEvent()->getItem();
                if (!$item->getData('sc_need_price_update')) {
                    return;
                }

                $products = json_decode($products, true);
                $result = [];
                $found = false;
                foreach ($products as $product) {
                    if (!$found) {
                        $allSkus = $product['allSkus'];
                        foreach ($allSkus as $sku) {
                            if ($item->getProduct()->getData($this->_helper->getProductIdAttribute()) == $sku || $item->getProductId() == $product['_realProductId']) {
                                $product['price'] = $this->getProductPrice->executeByItem($item);
                                $found = true;
                                break;
                            }
                        }
                    }
                    $result[] = $product;
                }
                $this->_coreSession->setProductToBasket(json_encode($result));
            } catch (\Exception $e) { }
        }
    }
}
