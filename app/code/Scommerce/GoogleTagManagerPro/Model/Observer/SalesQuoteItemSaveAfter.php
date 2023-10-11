<?php
/**
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GoogleTagManagerPro\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Scommerce\GoogleTagManagerPro\Helper\Data;


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

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper,
        SessionManagerInterface $coreSession
    ) {
        $this->_helper = $helper;
        $this->_coreSession = $coreSession;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isEnhancedEcommerceEnabled() && $this->_helper->isEnabled()) {
            try {
                $products = $this->_coreSession->getProductToBasket();
                if (!$products) return;

                $item = $observer->getEvent()->getItem();
                if (!$item->getData('sc_need_price_update')) return;

                $products = json_decode($products, true);
                $result = [];
                $found = false;
                foreach ($products as $product) {
                    if (!$found) {
                        $allSkus = $product['allSkus'];
                        foreach ($allSkus as $sku) {
                            if ($item->getSku() == $sku) {
                                $product['price'] = $item->getPriceInclTax();
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
