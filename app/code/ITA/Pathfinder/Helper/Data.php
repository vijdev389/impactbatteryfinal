<?php

namespace ITA\Pathfinder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{

    /** Admin configuration paths */
    //const XML_PATH_ENABLED                  = 'scommerce_trackingbase/general/active';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    protected $customfinderFactory;

    /**
     * @var Template
     */
    protected $_block;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Template $block
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \ITA\Pathfinder\Model\CustomfinderFactory $customfinderFactory,
        Template $block
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
		$this->customfinderFactory = $customfinderFactory;
        $this->_block = $block;
    }
	
	public function getPathfinderDetailsBySku($sku)
	{
		echo "dddddd";die;
		$collection = $this->customfinderFactory->create()->getCollection();
		$data = $collection->addFieldToFilter("sku",['eq' => $sku]);
		$finderDataArray = [];$finderData = [];	
		if($data->getSize()){
			foreach($data->getData() as $fdata){
				$finderData['vehicle_type'] = $fdata['vehicle_type'];
				$finderData['make'] = $fdata['make'];
				$finderData['model'] = $fdata['model'];
				$finderData['year'] = $fdata['year'];
				$finderDataArray = finderData;
			}
		}
		
		return $finderDataArray;
	}
} 
