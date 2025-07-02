<?php

namespace Scommerce\TrackingBase\Block\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Order Confirmation Block
 */
class Order extends Template
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var OrderCollection
     */
    protected $_orderCollection;

    /**
     * @param Context $context
     * @param Data $helper
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        Data $helper,
        OrderCollection $orderCollection,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_orderCollection = $orderCollection;
        parent::__construct($context, $data);
    }

    /**
     * @return DataObject|null
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
     * @return Data
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

    /**
     * @param $id
     * @return DataObject
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
        return substr(hash('md5' ,$id . $incId), 0, 10);
    }
}
