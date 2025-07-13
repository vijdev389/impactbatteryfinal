<?php
namespace ITA\Pathfinder\Block;
class Pathfinder extends \Magento\Framework\View\Element\Template
{	
	protected $customfinderFactory;
		
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,		
		\ITA\Pathfinder\Model\CustomfinderFactory $customfinderFactory,
		array $data = []
	)
	{
		$this->customfinderFactory = $customfinderFactory;
		parent::__construct($context, $data);
	}
	
	
	
	public function getPathfinderDetailsBySku($sku)
	{
		$collection = $this->customfinderFactory->create()->getCollection();
		$data = $collection->addFieldToFilter("sku",['eq' => $sku]);
		return $data;
	}
}
?>