<?php

namespace ITA\Pathfinder\Controller\Adminhtml\Customfinder;

class Importcsv extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;
	
	private $customfinderFactory;
	
	/**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;
	
	protected $messageManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\ITA\Pathfinder\Model\CustomfinderFactory $customfinderFactory,
		\Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager
		
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->customfinderFactory = $customfinderFactory;
		$this->resultJsonFactory = $jsonResultFactory;
		$this->messageManager = $messageManager;
    }

    /**
     * Mapped eBay Order List page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
		// enter the number of data fields you require the product row inside the CSV file to contain
		$required_data_fields = 4;
		$file = fopen($_FILES['import']['tmp_name'], "r");
		$headers = fgetcsv($file, 30000000, ","); // get data headers and skip 1st row
		$connection = $this->customfinderFactory->create()->getCollection()->getConnection();
		$tableName = $this->customfinderFactory->create()->getCollection()->getMainTable();
		$connection->truncateTable($tableName);
		//var_dump($tableName);die;
		$result = $this->resultJsonFactory->create();
		$message = "Fail";
		
		while ( $row = fgetcsv($file, 30000000, ",") ) {

			$data_count = count($row);
			if ($data_count < 1) {
				continue;
			}

			$data = array();
			$data = array_combine($headers, $row);
			$model = trim($data['Model']);
			$year = trim($data['Year']);
			$make = trim($data['Make']);
			$vehicle_type = trim($data['Vehicle Type']);
			$sku = trim($data['SKU']);
			if ($data_count < $required_data_fields) {
				$logger->info("Skipping product sku " . $sku . ". Not enough data to import.");
				continue;
			}
			//var_dump($data);die;
			
			//echo $model . '<br>' .$sku ;die;
			try {
				$collection = $this->customfinderFactory->create();
				$collection->setVehicleType($vehicle_type);
				$collection->setYear($year);
				$collection->setMake($make);
				$collection->setModel($model);
				$collection->setSku($sku);
				$collection->save();
				$result->setData(['mesaage' => "Success"]);
				$message = "Success";
			} 
			catch (\Exception $e) {
				$result->setData(['mesaage' => "Fail"]);
				$message = "Fail";
				//$logger->info("Invalid product SKU: ".$sku);
				continue;
			}
		}
		//$resultRedirect = $this->resultRedirectFactory->create();
		//$resultRedirect->setPath('');
		//var_dump($message);die;
		if($message == "Success"){
			//$this->messageManager->addSuccess(__('Csv Imported Successfully.'));
			//return $resultRedirect;
		}else{
			//$this->messageManager->addError(__('Failed to import csv.'));
			//return $resultRedirect;
		}
		return $result; 
    }

    /**
     * Check Order Import Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ITA_Pathfinder::grid_list');
    }
}