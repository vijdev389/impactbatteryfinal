<?php

/**
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GoogleTagManagerPro\Model;

use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Session
 * @package Scommerce\GoogleTagManagerPro\Model
 */
class Session
{
    const LAST_CATEGORY_ID_KEY = 'lastCategoryId';
    const PRODUCTS_KEY = 'products';
    const LIST_KEY = 'list';
    const DEFAULT_LIST = 'DEFAULT LIST';

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Session constructor.
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @param $idProduct
     * @return mixed|null
     */
    public function getCategoryForProduct($idProduct)
    {
        $data = $this->getTrackingData();
        foreach ($data[self::PRODUCTS_KEY] as $idP => $idCategory) {
            if ($idP == $idProduct) return $idCategory;
        }
        return null;
    }

    /**
     * @param $idProduct
     */
    public function unsetProductData($idProduct)
    {
        $data = $this->getTrackingData();
        if (isset($data[self::PRODUCTS_KEY]) && isset($data[self::PRODUCTS_KEY][$idProduct])) {
            unset($data[self::PRODUCTS_KEY][$idProduct]);
        }
        if (isset($data[self::LIST_KEY]) && isset($data[self::LIST_KEY][$idProduct])) {
            unset($data[self::LIST_KEY][$idProduct]);
        }
        $this->setTrackingData($data);
    }

    public function clearTrackingData()
    {
        $data = $this->getTrackingData();
        if (isset($data[self::PRODUCTS_KEY])) {
            $data[self::PRODUCTS_KEY] = [];
        }
        if (isset($data[self::LIST_KEY])) {
            $data[self::LIST_KEY] = [];
        }
        $this->setTrackingData($data);
    }

    /**
     * @return mixed|null
     */
    public function getLastCategory()
    {
        $data = $this->getTrackingData();
        return key_exists(self::LAST_CATEGORY_ID_KEY, $data) ? $data[self::LAST_CATEGORY_ID_KEY] : null;
    }

    /**
     * @param $idProduct
     * @param $idCategory
     */
    public function setCategoryForProduct($idProduct, $idCategory)
    {
        $data = $this->getTrackingData();
        $data[self::PRODUCTS_KEY][$idProduct] = $idCategory;
        $data[self::LAST_CATEGORY_ID_KEY] = $idCategory;
        $this->setTrackingData($data);
    }

    /**
     * @param $idProduct
     * @param $idCategory
     */
    public function setImpressionForProduct($idProduct, $list)
    {
        $data = $this->getTrackingData();
        $data[self::LIST_KEY][$idProduct] = $list;
        $this->setTrackingData($data);
    }

    /**
     * @param $idProduct
     * @param false $returnDefault
     * @return mixed|null
     */
    public function getImpressionForProduct($idProduct, $returnDefault = false)
    {
        $data = $this->getTrackingData();
        if (!array_key_exists(self::LIST_KEY, $data)) return $returnDefault ? '' : null;
        $list = $data[self::LIST_KEY];
        return key_exists($idProduct, $list) ? $list[$idProduct] : ($returnDefault ? '' : null);
    }

    /**
     * @param $categoryId
     */
    public function setLastCategoryId($categoryId)
    {
        $data = $this->getTrackingData();
        $data[self::LAST_CATEGORY_ID_KEY] = $categoryId;
        $this->setTrackingData($data);
    }

    /**
     * @param $data
     */
    protected function setTrackingData($data)
    {
        $this->customerSession->setScGtmTracking($data);
    }

    /**
     * @return array
     */
    protected function getTrackingData()
    {
        $data = $this->customerSession->getScGtmTracking();
        return $data == null ? [self::PRODUCTS_KEY => [], self::LAST_CATEGORY_ID_KEY => null, self::LIST_KEY => []] : $data;
    }
}
