<?php
/**
 * Scommerce TrackingBase View model for order view page
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\ViewModel\Adminhtml;

use Magento\Framework\DataObject;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class ViewOrder
 * @package Scommerce\TrackingBase\ViewModel\Adminhtml
 */
class ViewOrder extends DataObject implements ArgumentInterface
{
    // This is a flag for initialised data
    const DATA_TYPE_EMPTY   = 'empty';

    // Tracking data contains purchase information
    const DATA_TYPE_PURCHASE   = 'purchase';

    // Tracking data contains refund information
    const DATA_TYPE_REFUND  = 'refund';

    /**
     * @var SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * Tracking data
     * @var null | array
     */
    private $_internalData = null;

    /**
     * Tracking event type
     *
     * @var null
     */
    private $_dataType = null;

    /**
     * ViewOrder constructor.
     * @param SessionManagerInterface $coreSession
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        SessionManagerInterface $coreSession,
        Data $helper,
        array $data = []
    ) {
        $this->_coreSession = $coreSession;
        $this->_helper = $helper;
        parent::__construct($data);
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        $this->_init();
        return $this->_dataType;
    }

    /**
     * @return null
     */
    public function getTrackingData()
    {
        $this->_init();
        return $this->_internalData;
    }

    /**
     * Initialisation of the tracking data
     */
    protected function _init()
    {
        if ($this->_dataType !== null) {
            return;
        }

        $orderData = $this->_coreSession->getOrderData();
        if ($orderData) {
            $this->_dataType = self::DATA_TYPE_PURCHASE;
            $this->_internalData = json_decode($orderData, true);
            $this->_coreSession->unsOrderData();
            return;
        }

        $refundData = $this->_coreSession->getRefundOrder();
        if ($refundData) {
            $this->_dataType = self::DATA_TYPE_REFUND;
            $this->_internalData = json_decode($refundData, true);
            $this->_coreSession->unsRefundOrder();
            return;
        }
        $this->_dataType = self::DATA_TYPE_EMPTY;
    }

    /**
     * @return bool
     */
    public function hasTrackingData(): bool
    {
        $type = $this->getDataType();
        return $type == self::DATA_TYPE_PURCHASE || $type == self::DATA_TYPE_REFUND;
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getAdminSource($storeId)
    {
        return $this->_helper->getAdminSource($storeId);
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getAdminMedium($storeId)
    {
        return $this->_helper->getAdminMedium($storeId);
    }
}
