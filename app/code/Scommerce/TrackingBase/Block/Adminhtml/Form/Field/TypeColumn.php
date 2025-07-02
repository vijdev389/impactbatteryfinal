<?php

namespace Scommerce\TrackingBase\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Scommerce\TrackingBase\Model\Source\StepType;

/**
 * Class StepColumn
 */
class TypeColumn extends Select
{
    /**
     * @var StepType
     */
    protected $stepType;

    /**
     * StepColumn constructor.
     * @param Context $context
     * @param StepType $checkoutStep
     * @param array $data
     */
    public function __construct(
        Context $context,
        StepType $stepType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->stepType = $stepType;
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
            $this->setOptions($this->stepType->toOptionArray());
        }
        return parent::_toHtml();
    }
}
