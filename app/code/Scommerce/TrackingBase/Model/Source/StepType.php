<?php
/**
 *  Scommerce TrackingBase Step Types options
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
class StepType implements OptionSourceInterface
{
    const STEP_SHIPMENT         = 2;
    const STEP_PAYMENT          = 3;

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STEP_SHIPMENT, 'label' => __('Shipment')],
            ['value' => self::STEP_PAYMENT, 'label' => __('Payment')],
        ];
    }
}
