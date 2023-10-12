<?php

namespace Scommerce\TrackingBase\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Scommerce\TrackingBase\Model\Source\CheckoutStep;

/**
 * Class StepColumn
 */
class StepColumn extends Select
{
    /**
     * @var CheckoutStep
     */
    protected $checkoutStep;

    /**
     * StepColumn constructor.
     * @param Context $context
     * @param CheckoutStep $checkoutStep
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutStep $checkoutStep,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutStep = $checkoutStep;
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->checkoutStep->toOptionArray());
        }
        return parent::_toHtml();
    }
}
