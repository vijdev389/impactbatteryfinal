<?php
/**
 * Get Product Category Service Model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Model;

use Magento\Catalog\Model\Product\Type;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class GetProductCategory
 * @package Scommerce\TrackingBase\Model
 */
class GetProductCategory
{
    /**
     * @var GetCategoryPath
     */
    protected $_getCategoryPath;

    /**
     * @var GetParentProduct
     */
    protected $_getParentProduct;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * GetProductCategory constructor.
     * @param GetCategoryPath $getCategoryPath
     * @param GetParentProduct $getParentProduct
     */
    public function __construct(
        GetCategoryPath $getCategoryPath,
        GetParentProduct $getParentProduct,
        Data $helper
    ) {
        $this->_getCategoryPath = $getCategoryPath;
        $this->_getParentProduct = $getParentProduct;
        $this->_helper = $helper;
    }

    /**
     * @param $product
     * @return string|null
     */
    public function execute($product)
    {
        $storeId = $product->getStoreId();
        $categoryAttribute = $this->_helper->getCategoryAttribute($storeId);
        $primaryCategory = $product->getResource()->getAttributeRawValue(
            $product->getId(),
            $categoryAttribute,
            $product->getStore()
        );
        if (!$this->_helper->getIsCategoryId($storeId)) {
            return is_string($primaryCategory) ? $primaryCategory : '';
        }
        if ($primaryCategory) {
            return $this->_getCategoryPath->execute($primaryCategory);
        }
        $_cats = $product->getCategoryIds();
        if (!count($_cats)) {
            return '';
        }
        $_categoryId = array_pop($_cats);
        if (!isset($_categoryId) && $product->getTypeId() == Type::TYPE_SIMPLE) {
            $_parentProduct = $this->_getParentProduct->execute($product->getId());
            if ($_parentProduct) {
                $_cats = $_parentProduct->getCategoryIds();
                $_categoryId = array_pop($_cats);
            }
        }

        return $this->_getCategoryPath->execute($_categoryId);
    }
}
