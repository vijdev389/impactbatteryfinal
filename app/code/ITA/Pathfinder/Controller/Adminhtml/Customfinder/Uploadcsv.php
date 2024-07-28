<?php

namespace ITA\Pathfinder\Controller\Adminhtml\Customfinder;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\ScopeInterface; 


class Uploadcsv extends \Magento\Backend\App\Action
{

    protected $fileSystem;

    protected $uploaderFactory;

    protected $request;

    protected $adapterFactory;
	
	private $customfinderFactory;

    private $jsonResultFactory;
	
	protected $messageManager;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        AdapterFactory $adapterFactory,
		\ITA\Pathfinder\Model\CustomfinderFactory $customfinderFactory,
		\Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager

    ) {
        parent::__construct($context);
        $this->fileSystem = $fileSystem;
		$this->uploaderFactory = $uploaderFactory;
		$this->request = $request;
		$this->scopeConfig = $scopeConfig;
		$this->adapterFactory = $adapterFactory;
		$this->customfinderFactory = $customfinderFactory;
		$this->resultJsonFactory = $jsonResultFactory;
		$this->messageManager = $messageManager;
    }

    public function execute()
    { 
		
		//var_dump($_POST,$_FILES);die;
         if ( (isset($_FILES['csvImport']['name'])) && ($_FILES['csvImport']['name'] != '') ) 
         {
            try 
           {    
				$message = "Fail";
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'csvImport']);
                $uploaderFactory->setAllowedExtensions(['csv', 'xls']);
                $uploaderFactory->setAllowRenameFiles(true);
                $uploaderFactory->setFilesDispersion(true);

                $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('ITA_Pathfinder_IMPORTDATA');
                $result = $uploaderFactory->save($destinationPath);
				$jsonResult = $this->resultJsonFactory->create();
                if (!$result) 
                   {
                     throw new LocalizedException
                     (
                        __('File cannot be saved to path: $1', $destinationPath)
                     );

                   }
                else
                    {   
                        $imagePath = 'ITA_Pathfinder_IMPORTDATA'.$result['file'];

                        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);

                        $destinationfilePath = $mediaDirectory->getAbsolutePath($imagePath);

                        /* file read operation */

                        $file = fopen($destinationfilePath, "r");

                        //$column = fgetcsv($file,30000000, ",");
						$connection = $this->customfinderFactory->create()->getCollection()->getConnection();
						$tableName = $this->customfinderFactory->create()->getCollection()->getMainTable();
						$connection->truncateTable($tableName);
						$required_data_fields = 5;
						$headers = fgetcsv($file, 30000000, ",");
						//var_dump($column);die; 
						while ( $row = fgetcsv($file) ) {

							$data_count = count($row);
							if ($data_count < 1) {
								continue;
							}
							//var_dump($row);
							$data = array();
							$data = array_combine($headers, $row);
							$model = trim($data['Model']);
							$year = trim($data['Year']);
							$make = trim($data['Make']);
							$vehicle_type = trim($data['Vehicle Type']);
							$sku = trim($data['SKU']);
							if ($data_count < $required_data_fields) {
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
								$jsonResult->setData(['mesaage' => "Success"]);
								$message = "Success";
							} 
							catch (\Exception $e) {
								$jsonResult->setData(['mesaage' => "Fail"]);
								$message = "Fail";
								//$logger->info("Invalid product SKU: ".$sku);
								continue;
							}
						}
						if($message == "Success"){
							$this->messageManager->addSuccess(__('Csv Imported Successfully.'));
						}else{
							$this->messageManager->addError(__('Failed to import csv.'));
						}
                        
					return $jsonResult;
                    }        	           

           } 
           catch (\Exception $e) 
          {   
               $this->messageManager->addError(__($e->getMessage()));
               $this->_redirect('*/customfinder/');
          }

         }
         else
         {
            $this->messageManager->addError(__("Please try again."));
            $this->_redirect('*/*/');
         }
    }
}