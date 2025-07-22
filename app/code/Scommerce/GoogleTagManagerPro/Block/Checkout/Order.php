<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Scommerce\GoogleTagManagerPro\Block\Checkout;

use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Scommerce\GoogleTagManagerPro\Model\Session;

/**
 * Order Confirmation Block
 */
class Order extends \Magento\Framework\View\Element\Template
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
     * @var OrderCollection
     */
    protected $_orderCollection;

    /**
     * @var Session
     */
    protected $_gtmSession;

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
        OrderCollection $orderCollection,
        \Magento\Framework\Session\SessionManagerInterface $gtmSession,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_itemResourceModel = $itemResourceModel;
        $this->_orderCollection = $orderCollection;
        $this->_gtmSession = $gtmSession;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\DataObject|null
     */
    public function getOrder()
    {
        if ($this->getFirstOrder()) {
            return $this->getFirstOrder();
        }
        $id = $this->_request->getParam('id');
        $id = trim($id, "/");
        $token = $this->_request->getParam('token');
        $token = trim($token, "/");
        if (!$id || !$token) {
            return null;
        }
        try {
            $order = $this->getOrderByIncrementId($id);
            if (!$order || !$order->getId()) {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
        $tokenForOrder = $this->getToken($order->getId(), $order->getIncrementId());
        if ($token != $tokenForOrder) {
            return null;
        }
        return $order;
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

    /**
     * @param $id
     * @return \Magento\Framework\DataObject
     */
    public function getOrderByIncrementId($id)
    {
        $collection = $this->_orderCollection->create();
        $collection->addFieldToFilter('increment_id', $id);
        return $collection->getFirstItem();
    }

    /**
     * @param $id
     * @param $incId
     * @return false|string
     */
    public function getToken($id, $incId)
    {
        return substr(hash('SHA512', $id . $incId), 0, 10);
    }
}
