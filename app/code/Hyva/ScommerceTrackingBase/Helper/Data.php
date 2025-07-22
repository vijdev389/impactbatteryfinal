<?php

namespace Hyva\ScommerceTrackingBase\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager;

class Data extends AbstractHelper
{
    const SCOMMERCE_WISHLIST_HELPER = "Scommerce\AjaxLoginWishlist\Helper\Data";

    protected ObjectManagerInterface $objectManager;

    protected Manager $moduleManager;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Manager $moduleManager
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    public function isScommerceAjaxWishlistEnabled($storeId = null)
    {
        if ($this->moduleManager->isEnabled("Hyva_ScommerceAjaxLoginWishlist")) {
            try {
                $wishlistHelper = $this->objectManager->get(self::SCOMMERCE_WISHLIST_HELPER);
                return $wishlistHelper->isEnabled($storeId) && $wishlistHelper->isAjaxWishListEnabled($storeId);
            } catch (\Exception $exception) {

            }
        }

        return false;
    }
}
