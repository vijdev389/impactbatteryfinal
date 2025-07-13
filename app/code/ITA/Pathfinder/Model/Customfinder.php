<?php
declare(strict_types=1);

namespace ITA\Pathfinder\Model;

use  ITA\Pathfinder\Api\Data\CustomfinderInterface;

class Customfinder extends \Magento\Framework\Model\AbstractModel implements CustomfinderInterface
{
	/**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'pathfinder_grid_records';
    /**
     * @var string
     */
    protected $_cacheTag = 'pathfinder_grid_records';
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'pathfinder_grid_records';
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('ITA\Pathfinder\Model\ResourceModel\Customfinder');
    }
    /**
     * Get id
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set entityId
     * @param string $entityId
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }
	
	/**
     * Get Vehicle Type
     * @return string|null
     */
    public function getVehicleType(){
		return $this->_get(self::VEHICLE_TYPE);
	}

    /**
     * Set vehicleType
     * @param string $vehicleType
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setVehicleType($vehicleType){
		return $this->setData(self::VEHICLE_TYPE, $vehicleType);
	}
	
	/**
     * Get Make
     * @return string|null
     */
    public function getMake(){
		return $this->_get(self::MAKE);
	}

    /**
     * Set Make
     * @param string $make
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setMake($make){
		return $this->setData(self::MAKE, $make);
	}

    /**
     * Get model
     * @return string|null
     */
    public function getModel(){
		return $this->_get(self::MODEL);
	}

    /**
     * Set model
     * @param string $model
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setModel($model){
		return $this->setData(self::MODEL, $model);
	}
	
	/**
     * Get year
     * @return string|null
     */
    public function getYear(){
		return $this->_get(self::YEAR);
	}

    /**
     * Set Year
     * @param string $year
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setYear($year){
		return $this->setData(self::YEAR, $year);
	}
	
    /**
     * Get sku
     * @return string|null
     */
    public function getSku(){
		return $this->_get(self::SKU);
	}

    /**
     * Set sku
     * @param string $sku
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setSku($sku){
		return $this->setData(self::SKU, $sku);
	}

    /**
     * Get createdAt
     * @return string|null
     */
    public function getCreatedAt(){
		return $this->_get(self::CREATED_AT);
	}

    /**
     * Set createdAt
     * @param string $createdAt
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setCreatedAt($createdAt){
		return $this->setData(self::CREATED_AT, $createdAt);
	}
	
	/**
     * Get updateAt
     * @return string|null
     */
    public function getUpdateAt(){
		return $this->_get(self::UPDATED_AT);
	}

    /**
     * Set updateAt
     * @param string $updateAt
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setUpdateAt($updateAt){
		return $this->setData(self::UPDATED_AT, $updateAt);
	}
    
}