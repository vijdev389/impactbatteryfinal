<?php
/**
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GoogleTagManagerPro\Model\Plugin;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;

class QuoteItemToOrderItem
{
	public function aroundConvert(
        ToOrderItem $subject,
        \Closure $proceed,
        AbstractItem $item,
        $additional = []
    )
	{
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
		$orderItem->setGoogleCategory($item->getGoogleCategory());
        return $orderItem;
    }
}