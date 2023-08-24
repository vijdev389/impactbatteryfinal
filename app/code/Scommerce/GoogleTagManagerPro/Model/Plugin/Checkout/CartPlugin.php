<?php

namespace Scommerce\GoogleTagManagerPro\Model\Plugin\Checkout;

use Scommerce\GoogleTagManagerPro\Helper\Data;

class CartPlugin
{
    protected $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function afterAddOrderItem($subject, $result)
    {
        if (!$this->helper->isEnabled() || !$this->helper->isEnhancedEcommerceEnabled()) return;

        foreach ($result->getItems() as $item) {
            $item->setScTrackingData(json_encode(['list' => 'Reorder']));
        }
        return $result;
    }
}
