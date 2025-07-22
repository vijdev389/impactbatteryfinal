<?php

namespace Scommerce\TrackingBase\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class ConsentParameters extends Select
{
    /**
     * SetInputName function
     *
     * @param [type] $value
     * @return void
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * SetInputId function
     *
     * @param [type] $value
     * @return void
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
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * GetSourceOptions function
     *
     * @return array
     */
    private function getSourceOptions()
    {
        return [
            ['label' => 'ad_storage', 'value' => 'ad_storage'],
            ['label' => 'ad_user_data', 'value' => 'ad_user_data'],
            ['label' => 'ad_personalization', 'value' => 'ad_personalization'],
            ['label' => 'analytics_storage', 'value' => 'analytics_storage'],
            ['label' => 'functionality_storage', 'value' => 'functionality_storage'],
            ['label' => 'personalization_storage', 'value' => 'personalization_storage'],
            ['label' => 'security_storage', 'value' => 'security_storage'],
        ];
    }
}
