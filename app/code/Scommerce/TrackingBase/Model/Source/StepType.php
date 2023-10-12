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
    const STEP_OTHER            = 1;
    const STEP_SHIPMENT         = 2;
    const STEP_PAYMENT          = 3;
    const STEP_USER_TYPE        = 4;
    const STEP_BILLING_CHECK    = 5;

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STEP_OTHER, 'label' => __('Other')],
            ['value' => self::STEP_SHIPMENT, 'label' => __('Shipment')],
            ['value' => self::STEP_PAYMENT, 'label' => __('Payment')],
            ['value' => self::STEP_USER_TYPE, 'label' => __('User Type')],
            ['value' => self::STEP_BILLING_CHECK, 'label' => __('BillingDeliveryCheckBox')],
        ];
    }
}
