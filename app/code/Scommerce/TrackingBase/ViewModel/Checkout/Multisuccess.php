<?php
/**
 * Multisuccess page view model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\ViewModel\Checkout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class Cart
 */
class Multisuccess extends Success
{
    /**
     * @return CartInterface|Quote
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getQuote()
    {
        return $this->_block->getOrder()->getQuote();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_block->getOrder();
    }
}
