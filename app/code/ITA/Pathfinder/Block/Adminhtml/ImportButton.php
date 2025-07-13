<?php
namespace ITA\Pathfinder\Block\Adminhtml;

class ImportButton extends \Magento\Backend\Block\Template {
	
	 /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'custom.phtml';
	 /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
    }
	
	/**
     * get form key
     *
     * @return string
     */
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }
}