<?php
/**
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GoogleTagManagerPro\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Scommerce\GoogleTagManagerPro\Helper\Data;


class SalesQuoteItemSetGoogleCategory implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper)
    {
        $this->_helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isEnhancedEcommerceEnabled() && $this->_helper->isEnabled()){
            $quoteItem = $observer->getQuoteItem();
            $product = $observer->getProduct();
            $category = $quoteItem->getGoogleCategory();

            if (!isset($category)){
                $category = str_replace('"','',$this->_helper->getCategoryFromCookie());

                if (!isset($category) || strlen($category)==0){
                    $category = $this->_helper->getProductCategoryName($product);
                }
                $quoteItem->setGoogleCategory($category);
            }
        }
    }
}