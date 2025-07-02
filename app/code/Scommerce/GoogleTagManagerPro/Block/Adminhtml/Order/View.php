<?php
/**
 * Google Tag Manager Pro block
 *
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GoogleTagManagerPro\Block\Adminhtml\Order;

use Magento\Framework\View\Element\Template;
use Scommerce\GoogleTagManagerPro\Helper\Data;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Order
 * @package Scommerce\GoogleTagManagerPro\Block\Adminhtml\Order
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var SessionManagerInterface
     */
    private $_coreSession;

    /**
     * Order constructor.
     * @param Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
        $this->_coreSession = $context->getSession();
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @return mixed|null
     */
    public function getOrderData()
    {
        $orderData = $this->_coreSession->getOrderData();
        if ($orderData) {
            $result = json_decode($orderData, true);
            return $result;
        }

        return null;
    }

    /**
     * Unset order data after processed
     */
    public function removeOrderData()
    {
        $this->_coreSession->unsOrderData();
    }

    /**
     * @return mixed|null
     */
    public function getRefundOrder()
    {
        $orderData = $this->_coreSession->getRefundOrder();
        if ($orderData) {
            $result = json_decode($orderData, true);
            return $result;
        }

        return null;
    }

    /**
     * Unset order data after processed
     */
    public function removeRefundOrderData()
    {
        $this->_coreSession->unsRefundOrder();
    }
}