<?php
/**
 * Cart view model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\ViewModel;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Scommerce\TrackingBase\Helper\Data;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Exception\LocalizedException;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetCategoryPath;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductId;
use Scommerce\TrackingBase\Model\GetProductPrice;

/**
 * Class Cart
 * @package Scommerce\TrackingBase\ViewModel
 */
class Cart extends DataObject implements ArgumentInterface
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
     * @var GetProductId
     */
    protected $_getProductId;

    /**
     * @param Data $helper
     * @param GetProductCategory $getCategory
     * @param GetBrand $getBrand
     * @param GetProductPrice $getProductPrice
     * @param GetCategoryPath $getCategoryPath
     * @param GetProductCategory $getProductCategory
     * @param array $data
     */
    public function __construct(
        Data $helper,
        GetProductCategory $getCategory,
        GetBrand $getBrand,
        GetProductPrice $getProductPrice,
        GetCategoryPath $getCategoryPath,
        GetProductCategory $getProductCategory,
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
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCrosssellProductsData()
    {
        $cross = $this->_block->getLayout()->getBlock('checkout.cart.crosssell');
        if (!$cross) {
            return [];
        }
        $items = $cross->getItems();
        if (!$items) {
            return [];
        }

        $_loop = 1;
        $_products = [];
        foreach ($items as $_product) {
            $_products[] = [
                'id' => $this->_helper->escapeJsQuote($this->_getProductId->execute($_product)),
                'name' => $this->_helper->escapeJsQuote(trim($_product->getName())),
                'category' => $this->_getProductCategory->execute($_product),
                'brand' => $this->_helper->escapeJsQuote($this->_getBrand->execute($_product)),
                'list' => $this->_helper->escapeJsQuote('Crosssell Products'),
                'price' => $this->_getProductPrice->execute($_product),
                'url' => $_product->getProductUrl(),
                'position' => $_loop
            ];
            $_loop++;
        }
        return $_products;
    }
}
