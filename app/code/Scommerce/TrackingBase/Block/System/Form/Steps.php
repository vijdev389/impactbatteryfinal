<?php
/**
 *  Scommerce Tracking Base Steps configuration block class
 *
 * @category   Scommerce
 * @package    Scommerce_TrackingBase
 * @author     Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Block\System\Form;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Scommerce\TrackingBase\Block\Adminhtml\Form\Field\StepColumn;
use Scommerce\TrackingBase\Block\Adminhtml\Form\Field\TypeColumn;
use Scommerce\TrackingBase\Model\Source\CheckoutStep;
use Scommerce\TrackingBase\Model\Source\StepType;

/**
 * Class Steps
 */
class Steps extends AbstractFieldArray
{
    /**
     * @var CheckoutStep
     */
    protected $_checkoutSteps;

    /**
     * @var StepType
     */
    protected $_stepType;

    protected $_stepRenderer;

    protected $_typeRenderer;

    /**
     * Steps constructor.
     * @param Context $context
     * @param CheckoutStep $checkoutSteps
     * @param StepType $stepType
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutStep $checkoutSteps,
        StepType $stepType,
        array $data = []
    ) {
        $this->_checkoutSteps  = $checkoutSteps;
        $this->_stepType  = $stepType;
        parent::__construct($context, $data);
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('selector', ['label' => __('Selector'), 'class' => 'required-entry selector']);
        $this->addColumn('type', ['label' => __('Type'), 'class' => 'required-entry type',
            'renderer' => $this->getTypeRenderer()]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Step');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $step = $row->getStep();
        if ($step !== null) {
            $options['option_' . $this->getStepRenderer()->calcOptionHash($step)] = 'selected="selected"';
        }
        $type = $row->getType();
        if ($type !== null) {
            $options['option_' . $this->getTypeRenderer()->calcOptionHash($type)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|StepColumn
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getStepRenderer()
    {
        if (!$this->_stepRenderer) {
            $this->_stepRenderer = $this->getLayout()->createBlock(
                StepColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_stepRenderer;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|TypeColumn
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTypeRenderer()
    {
        if (!$this->_typeRenderer) {
            $this->_typeRenderer = $this->getLayout()->createBlock(
                TypeColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_typeRenderer;
    }
}
