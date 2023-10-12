<?php
/**
 * Get Product Sku By Id Service Model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Model;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class GetProductSkuById
 * @package app\code\Scommerce\TrackingBase\Model
 */
class GetProductSkuById
{
    /**
     * @var CollectionFactory
     */
    protected $_productCollection;

    /**
     * @var Product
     */
    protected $_resource;

    /**
     * GetProductSkuById constructor.
     * @param CollectionFactory $productCollection
     * @param Product $resource
     */
    public function __construct(
        CollectionFactory $productCollection,
        Product $resource
    ) {
        $this->_productCollection = $productCollection;
        $this->_resource = $resource;
    }

    /**
     * @param $productId
     * @return array|mixed|null
     */
    public function execute($productId, $attribute = 'sku', $storeId = 0)
    {
        if ($attribute == 'entity_id') {
            return $productId;
        } elseif ($attribute == 'sku') {
            $collection = $this->_productCollection->create();
            $collection
                ->addFieldToSelect('sku')
                ->addFieldToFilter('entity_id', $productId);
            $item = $collection->getFirstItem();
            if ($item && $item->getData('sku')) {
                return $item->getData('sku');
            }
        } else {
            $value = $this->_resource->getAttributeRawValue($productId, $attribute, $storeId);
            if ($value) {
                return $value;
            }
        }

        return null;
    }
}
