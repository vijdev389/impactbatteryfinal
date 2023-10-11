<?php
/**
 * Method Block
 *
 * @category   Scommerce
 * @package    Scommerce_GoogleTagManagerPro
 * @author     Scommerce Mage
 *
 */

namespace Scommerce\GoogleTagManagerPro\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class CookieOption
 * @package Scommerce\GoogleTagManagerPro\Block\Adminhtml\Form\Field
 */
class CookieOption extends AbstractFieldArray
{
    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'query_param',
            [
                'label' => __('Query Param'),
                'class' => 'required-entry'
            ]
        );
        $this->addColumn(
            'cookie_name',
            [
                'label' => __('Cookie name'),
                'class' => 'required-entry'
            ]
        );
        $this->addColumn(
            'cookie_value',
            [
                'label' => __('Cookie Value'),
                'class' => 'require-entry'
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Config');

        parent::_prepareToRender();
    }
}
