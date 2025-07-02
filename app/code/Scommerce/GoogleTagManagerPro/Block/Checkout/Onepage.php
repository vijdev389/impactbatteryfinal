<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Scommerce\GoogleTagManagerPro\Block\Checkout;

/**
 * Checkout One Page Block
 */
class Onepage extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Scommerce\GoogleTagManagerPro\Helper\Data
     */
    protected $_helper;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Scommerce\GoogleTagManagerPro\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\GoogleTagManagerPro\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * Return quote items
     *
     * @return \Magento\Quote\Model\Quote\Item
     */

    public function getCartItems()
    {
        $quote = $this->getQuote();
        $cartItems = $quote->getAllItems();

        return $cartItems;
    }

    /**
     * Return quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Return helper object
     *
     * @return \Scommerce\GoogleTagManagerPro\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Render block html if google tag manager is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_helper->isEnabled() ? parent::_toHtml() : '';
    }
}
