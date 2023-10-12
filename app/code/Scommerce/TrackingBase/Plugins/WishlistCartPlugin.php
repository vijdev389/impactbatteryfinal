<?php
/**
 * Plugin for add to cart from wishlist
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Plugins;

use Magento\Framework\Registry;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class WishlistCartPlugin
 * @package Scommerce\TrackingBase\Plugins
 */
class WishlistCartPlugin
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * WishlistCartPlugin constructor.
     * @param Data $helper
     * @param Registry $registry
     */
    public function __construct(
        Data $helper,
        Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * @param $subject
     * @return null
     */
    public function beforeExecute($subject)
    {
        if (!$this->helper->isEnabled() || !$this->helper->isEnhancedEcommerceEnabled()) return null;
        $this->registry->register(Data::WISHLIST_REGISTRY, 1);

        return null;
    }
}
