<?php

namespace Scommerce\TrackingBase\Block\Checkout;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template\Context;
use Magento\Multishipping\Block\Checkout\Success;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class Multisuccess
 */
class Multisuccess extends Success
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Multisuccess constructor.
     * @param Context $context
     * @param Multishipping $multishipping
     * @param Data $helper
     * @param Order $order
     * @param array $data
     */
    public function __construct(
        Context $context,
        Multishipping $multishipping,
        Data $helper,
        Order $order,
        array $data = []
    ) {
        parent::__construct($context, $multishipping, $data);
        $this->order = $order;
        $this->helper = $helper;
    }

    /**
     * @return DataObject
     */
    public function getOrder()
    {
        $ids = $this->getOrderIds();
        foreach ($ids as $id) {
            return $this->order->getOrderByIncrementId($id);
        }
    }

    /**
     * @return array
     */
    public function getAdditionalOrders()
    {
        $ids = $this->getOrderIds();
        $result = [];
        $counter = 0;
        foreach ($ids as $id => $incId) {
            if ($counter == 0) {
                $counter++;
                continue;
            }
            $result[$id] = $incId;
        }
        return $result;
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @param $id
     * @param $incId
     * @return false|string
     */
    public function getToken($id, $incId)
    {
        return $this->order->getToken($id, $incId);
    }
}
