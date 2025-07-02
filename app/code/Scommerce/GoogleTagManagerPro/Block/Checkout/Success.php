<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Scommerce\GoogleTagManagerPro\Block\Checkout;

use Magento\Sales\Model\Order\Item;

/**
 * Order Confirmation Block
 */
class Success extends \Magento\Framework\View\Element\Template
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
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item
     */
    protected $_itemResourceModel;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $_quoteItemFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Scommerce\GoogleTagManagerPro\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\GoogleTagManagerPro\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_itemResourceModel = $itemResourceModel;
        parent::__construct($context, $data);
    }

    /**
     * Return quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
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
     * Render block html if google universal analytics is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_helper->isEnabled() ? parent::_toHtml() : '';
    }

    public function getQuoteItem($quoteItemId)
    {
        $quoteItem = $this->_quoteItemFactory->create();
        $this->_itemResourceModel->load($quoteItem, $quoteItemId, 'item_id');

        return $quoteItem;
    }

    /**
     * @param $item Item
     */
    public function needSkipItem($item)
    {
        $productType = $item->getProductType();
        if ($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            || ($item->getParentItemId() && $item->getParentItem()->getProductType() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        ) {
            return true;
        }
        return false;
    }
}
