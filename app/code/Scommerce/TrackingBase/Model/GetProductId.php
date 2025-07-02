<?php
/**
 *
 *
 * @category
 * @package
 * @author
 */

namespace Scommerce\TrackingBase\Model;

use Scommerce\TrackingBase\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class GetProductId
 * @package Scommerce\TrackingBase\Model
 */
class GetProductId
{
    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var GetProductSkuById
     */
    private $_getProductSkuById;

    /**
     * GetProductId constructor.
     * @param GetProductSkuById $getProductSkuById
     * @param Data $helper
     */
    public function __construct(
        GetProductSkuById $getProductSkuById,
        Data $helper
    ) {
        $this->_helper = $helper;
        $this->_getProductSkuById = $getProductSkuById;
    }

    /**
     * @param $item
     * @param bool $checkChildren
     * @return array|mixed|string|null
     */
    public function execute($item, $checkChildren = true)
    {
        if ($item instanceof QuoteItem) {
            return $this->getQuoteItemId($item, $checkChildren);
        } elseif ($item instanceof OrderItem) {
            return $this->getOrderItemId($item, $checkChildren);
        }
        return $this->getProductId($item);
    }

    /**
     * @param $productId
     * @param $storeId
     * @return array|mixed|null
     */
    public function getAttributeIdValue($productId, $storeId = null)
    {
        $idAttribute = $this->_helper->getProductIdAttribute($storeId);
        return $this->_getProductSkuById->execute($productId, $idAttribute, $storeId);
    }

    /**
     * @param $item QuoteItem|OrderItem
     * @return array
     */
    public function getIdsForItem($item)
    {
        $_realId = $item->getProduct()->getId();
        $id = $this->getAttributeIdValue($item->getProduct()->getId());
        $allSkus = [$id];
        if ($item->getProductType() == 'configurable') {
            $children = $item instanceof QuoteItem ? $item->getChildren() : $item->getChildrenItems();
            if (is_array($children) && count($children)) {
                $_realId = $children[0]->getProduct()->getId();
                $childId = $this->getAttributeIdValue($_realId);
                $allSkus = array_merge($allSkus, [$childId]);
                if (!$this->_helper->sendParentSKU()) {
                    $id = $childId;
                }
            }
        }
        return [$id, $allSkus, $_realId];
    }

    /**
     * @param $product Product
     * @return mixed
     */
    protected function getProductId(Product $product)
    {
        return $product->getData($this->_helper->getProductIdAttribute($product->getStoreId()));
    }

    /**
     * @param QuoteItem $item
     * @param $checkChildren
     * @return array|mixed|string|null
     */
    protected function getQuoteItemId(QuoteItem $item, $checkChildren)
    {
        $idAttribute = $this->_helper->getProductIdAttribute($item->getStoreId());
        if ($this->_helper->sendParentSKU() && $checkChildren) {
            if ($item->getChildren()) {
                if ($idAttribute == 'sku') {
                    return $this->_getProductSkuById->execute($item->getProductId());
                }
                return $item->getChildren()[0]->getProduct()->getData($idAttribute);
            }
        }
        if ($idAttribute == 'sku') {
            return $item->getSku();
        }
        return $item->getProduct()->getData($idAttribute);
    }

    /**
     * @param OrderItem $item
     * @param $checkChildren
     * @return array|mixed|string|null
     */
    protected function getOrderItemId(OrderItem $item, $checkChildren)
    {
        $idAttribute = $this->_helper->getProductIdAttribute($item->getStoreId());
        if ($this->_helper->sendParentSKU() && $checkChildren) {
            if ($item->getChildrenItems()) {
                if ($idAttribute == 'sku') {
                    return $this->_getProductSkuById->execute($item->getProductId());
                }
                return $item->getChildrenItems()[0]->getProduct()->getData($idAttribute);
            }
        }
        if ($idAttribute == 'sku') {
            return $item->getSku();
        }
        return $item->getProduct()->getData($idAttribute);
    }
}
