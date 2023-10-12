<?php
/**
 *  Scommerce TrackingBase Steps options
 *
 * @category   Scommerce
 * @package    Scommerce_TrackingBase
 * @author     Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CheckoutSteps
 */
class CheckoutStep implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Step 1')],
            ['value' => 2, 'label' => __('Step 2')],
            ['value' => 3, 'label' => __('Step 3')],
            ['value' => 4, 'label' => __('Step 4')],
            ['value' => 5, 'label' => __('Step 5')],
            ['value' => 6, 'label' => __('Step 6')],
        ];
    }
}
