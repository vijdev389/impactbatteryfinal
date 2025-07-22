<?php

namespace Scommerce\TrackingBase\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Scommerce\TrackingBase\Block\Adminhtml\Form\Field\YesNoDynamicRow;
use Magento\Framework\DataObject;
use Scommerce\TrackingBase\Block\Adminhtml\Form\Field\ConsentParameters;

class CookieMapping extends AbstractFieldArray
{

    private $templeteRenderer;

    private $consentRenderer;
    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'consent_param',
            [
                'label' => __('Consent Param'),
                'renderer' => $this->getConsentRenderer()
            ]
        );
        $this->addColumn(
            'default_value', [
            'label' => __('Default Value'),
            'renderer' => $this->getTempleteRenderer()
        ]);
        $this->addColumn(
            'cookie_name',
            [
                'label' => __('Cookie name'),
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');

        parent::_prepareToRender();
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $templete = $row->getTemplete();
        if ($templete !== null) {
            $options['option_' . $this->getTempleteRenderer()->calcOptionHash($templete)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    private function getTempleteRenderer()
    {
        if (!$this->templeteRenderer) {
            $this->templeteRenderer = $this->getLayout()->createBlock(
                YesNoDynamicRow::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->templeteRenderer;
    }

    private function getConsentRenderer()
    {
        if (!$this->consentRenderer) {
            $this->consentRenderer = $this->getLayout()->createBlock(
                ConsentParameters::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->consentRenderer;
    }
}
