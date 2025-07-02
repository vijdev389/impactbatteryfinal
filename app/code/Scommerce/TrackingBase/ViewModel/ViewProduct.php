<?php
/**
 * Product page view model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\ViewModel;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Scommerce\TrackingBase\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetCategoryPath;
use Scommerce\TrackingBase\Model\GetParentProduct;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductId;
use Scommerce\TrackingBase\Model\GetProductPrice;

/**
 * Class ListProduct
 * @package Scommerce\TrackingBase\ViewModel
 */
class ViewProduct extends DataObject implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Template
     */
    protected $_block;

    /**
     * @var GetProductCategory
     */
    protected $_getCategory;

    /**
     * @var GetBrand
     */
    protected $_getBrand;

    /**
     * @var GetProductPrice
     */
    protected $_getProductPrice;

    /**
     * @var GetCategoryPath
     */
    protected $_getCategoryPath;

    /**
     * @var GetProductCategory
     */
    protected $_getProductCategory;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollection;

    /**
     * @var GetParentProduct
     */
    protected $_getParentProduct;

    /**
     * @var GetProductId
     */
    protected $_getProductId;

    /**
     * ViewProduct constructor.
     * @param Data $helper
     * @param GetProductCategory $getCategory
     * @param GetBrand $getBrand
     * @param GetProductPrice $getProductPrice
     * @param GetCategoryPath $getCategoryPath
     * @param GetProductCategory $getProductCategory
     * @param Registry $registry
     * @param ProductCollectionFactory $productCollection
     * @param array $data
     */
    public function __construct(
        Data $helper,
        GetProductCategory $getCategory,
        GetBrand $getBrand,
        GetProductPrice $getProductPrice,
        GetCategoryPath $getCategoryPath,
        GetProductCategory $getProductCategory,
        Registry $registry,
        ProductCollectionFactory $productCollection,
        GetParentProduct $getParentProduct,
        GetProductId $getProductId,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_getCategory = $getCategory;
        $this->_getBrand = $getBrand;
        $this->_getProductPrice = $getProductPrice;
        $this->_getCategoryPath = $getCategoryPath;
        $this->_getProductCategory = $getProductCategory;
        $this->_getProductId = $getProductId;
        $this->_registry = $registry;
        $this->_productCollection = $productCollection;
        $this->_getParentProduct = $getParentProduct;
        parent::__construct($data);
    }

    /**
     * @param $block
     */
    public function setBlock($block)
    {
        $this->_block = $block;
    }

    /**
     * Return helper object
     *
     * @return Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Return catalog product collection
     *
     * @param $_productIds
     * @return ProductCollection
     */
    public function getProducts($_productIds)
    {
        $idAttribute = $this->_helper->getProductIdAttribute();
        $attributes = ['name', 'sku', 'price'];
        $attributes = array_merge($attributes, [$idAttribute]);
        $collection = $this->_productCollection->create();
        return $collection
            ->addAttributeToSelect($attributes)
            ->addAttributeToFilter('entity_id', ['in' => $_productIds])
            ->addUrlRewrite();
    }

    /**
     * @return array
     */
    public function getRelatedProducts()
    {
        $ids = $this->getProduct()->getRelatedProductIds();
        return $this->getProductsCollection($ids, 'Related Products');
    }

    /**
     * @return array
     */
    public function getUpsellProducts()
    {
        $ids = $this->getProduct()->getUpSellProductIds();
        return $this->getProductsCollection($ids, 'Upsell Products');
    }

    /**
     * @param $ids
     * @param $listName
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductsCollection($ids, $listName)
    {
        $idAttribute = $this->_helper->getProductIdAttribute();
        $_loop = 1;
        $_productCollection = $this->getProducts($ids);
        $_products = [];
        foreach ($_productCollection as $_product) {
            $_products[] = [
                'id' => $this->_helper->escapeJsQuote($_product->getData($idAttribute)),
                'name' => $this->_helper->escapeJsQuote(trim($_product->getName())),
                'category' => $this->_getProductCategory->execute($_product),
                'brand' => $this->_helper->escapeJsQuote($this->_getBrand->execute($_product)),
                'list' => $this->_helper->escapeJsQuote($listName),
                'price' => $this->_getProductPrice->execute($_product),
                'url' => $_product->getProductUrl(),
                'position' => $_loop
            ];
            $_loop++;
        }
        return $_products;
    }

    /**
     * @return mixed|null
     */
    public function getProduct()
    {
        return $this->_registry->registry('product');
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductData()
    {
        $_product = $this->getProduct();
        return [
            'name' => $this->_helper->escapeJsQuote(trim($_product->getName())),
            'id' => $this->_helper->escapeJsQuote($_product->getData($this->_helper->getProductIdAttribute($_product->getStoreId()))),
            'price' => $this->_getProductPrice->execute($_product),
            'brand' => $this->_helper->escapeJsQuote($this->_getBrand->execute($_product)),
            'category' => $this->_helper->escapeJsQuote($this->_getProductCategory->execute($_product))
        ];
    }

    /**
     * @return bool
     */
    public function isNeedRender()
    {
        return $this->_helper->isEnabled() && $this->_helper->isEnhancedEcommerceEnabled();
    }

    /**
     * @return array|string|string[]|null
     * @throws NoSuchEntityException
     */
    public function getRemarketingCategories()
    {
        $_product = $this->getProduct();
        $primaryCategory = $_product->getResource()->getAttributeRawValue(
            $_product->getId(),
            'product_primary_category',
            $_product->getStore()
        );
        if ($primaryCategory) {
            return $this->_getCategoryPath->execute($primaryCategory, true);
        }
        $_cats = $_product->getCategoryIds();
        $_categoryId = array_pop($_cats);
        if (!isset($_categoryId) && $_product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $_parentProduct = $this->_getParentProduct->execute($_product->getId());
            if ($_parentProduct) {
                $_cats = $_parentProduct->getCategoryIds();
                $_categoryId = array_pop($_cats);
            }
        }
        if ($_categoryId) {
            return $this->_getCategoryPath->execute($_categoryId, true);
        }
        return [
            'full' => '',
            'plain' => ''
        ];
    }
}
