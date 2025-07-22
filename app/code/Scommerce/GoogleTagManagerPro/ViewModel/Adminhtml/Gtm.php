<?php
/**
 * Scommerce GoogleTagManagerPro View model for order view page
 *
 * @category Scommerce
 * @package Scommerce_GoogleTagManagerPro
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\GoogleTagManagerPro\ViewModel\Adminhtml;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Scommerce\GoogleTagManagerPro\Helper\Data;

/**
 * Class Gtm
 * @package Scommerce\GoogleTagManagerPro\ViewModel\Adminhtml
 */
class Gtm implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $_helper;

    /**
     * Gtm constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getAccountId($storeId)
    {
        return $this->_helper->getAccountId($storeId);
    }

    /**
     * @param $storeId
     */
    public function getGtmHelper()
    {
        return $this->_helper;
    }
}
