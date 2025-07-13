<?php
declare(strict_types=1);

namespace ITA\Pathfinder\Api\Data;

interface CustomfinderInterface 
{
	const ENTITY_ID = 'entity_id';
    const VEHICLE_TYPE = 'vehicle_type';
    const MODEL = 'model';
	const MAKE = 'make';
    const YEAR = 'year';
    const SKU = 'sku';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get EntityId
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entityId
     * @param string $id
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setEntityId($entityId);

    /**
     * Get Vehicle Type
     * @return string|null
     */
    public function getVehicleType();

    /**
     * Set vehicleType
     * @param string $vehicleType
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setVehicleType($vehicleType);
	
	/**
     * Get Make
     * @return string|null
     */
    public function getMake();

    /**
     * Set Make
     * @param string $make
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setMake($make);

    /**
     * Get model
     * @return string|null
     */
    public function getModel();

    /**
     * Set model
     * @param string $model
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setModel($model);
	
	/**
     * Get year
     * @return string|null
     */
    public function getYear();

    /**
     * Set Year
     * @param string $year
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setYear($year);
	
    /**
     * Get sku
     * @return string|null
     */
    public function getSku();

    /**
     * Set sku
     * @param string $sku
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setSku($sku);

    /**
     * Get createdAt
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set createdAt
     * @param string $createdAt
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setCreatedAt($createdAt);
	
	/**
     * Get updateAt
     * @return string|null
     */
    public function getUpdateAt();

    /**
     * Set updateAt
     * @param string $updateAt
     * @return \ITA\Pathfinder\Api\Data\CustomfinderInterface
     */
    public function setUpdateAt($updateAt);
}
	