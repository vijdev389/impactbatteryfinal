<?php
/**
 * Product List View model
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
 * Class ListProduct
 * @package Scommerce\TrackingBase\ViewModel
 */
class ListProduct extends DataObject implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Layer
     */
    protected $_layer;

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
     * @param Resolver $layer
     * @param GetProductCategory $getCategory
     * @param GetBrand $getBrand
     * @param GetProductPrice $getProductPrice
     * @param GetCategoryPath $getCategoryPath
     * @param GetProductCategory $getProductCategory
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Resolver $layer,
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
        $this->_layer = $layer->get();
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
     * @return LayoutInterface
     * @throws LocalizedException
     */
    public function getLayout()
    {
        return $this->_block->getLayout();
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getProductCollection()
    {
        if ($this->getMode() == "category")
            return $this->getLayout()->getBlockSingleton('Magento\Catalog\Block\Product\ListProduct')->getLoadedProductCollection();
        else
            return $this->getLayout()->getBlockSingleton('Magento\CatalogSearch\Block\SearchResult\ListProduct')->getLoadedProductCollection();
    }

    /**
     * Retrieve constant display mode PAGE
     *
     * @return string
     */
    public function getCMDisplayMode()
    {
        return 'PAGE';
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
    public function getProductsData()
    {
        $_loop = 1;
        $_productCollection = $this->getProductCollection();
        $_products = [];
        $_imperssionList = $this->getImpressionList($this->getCategory());
        foreach ($_productCollection as $_product) {
            $_products[] = [
                'id' => $this->_helper->escapeJsQuote($this->_getProductId->execute($_product)),
                'name' => $this->_helper->escapeJsQuote(trim($_product->getName())),
                'category' => $this->_getProductCategory->execute($_product),
                'brand' => $this->_helper->escapeJsQuote($this->_getBrand->execute($_product)),
                'list' => $this->_helper->escapeJsQuote($_imperssionList),
                'price' => $this->_getProductPrice->execute($_product),
                'url' => $_product->getProductUrl(),
                'position' => $_loop
            ];
            $_loop++;
        }
        return $_products;
    }

    /**
     * @param $category
     * @return string
     */
    public function getImpressionList($category)
    {
        $_mode = $this->getMode();
        if ($_mode == 'category') {
            return $this->_helper->getSendFullList() ? 'Category - ' . $category : $category;
        } elseif ($_mode == 'search') {
            return 'Search Results';
        }
    }

    /**
     * @return false|string|null
     */
    public function getCategory()
    {
        $_category = $this->_layer->getCurrentCategory();
        if ($_category->getDisplayMode() == $this->getCMDisplayMode()) return false;

        if ($_category) {
            $categoryPath = $this->_getCategoryPath->execute($_category, true);
            if ($this->_helper->getSendFullList()){
                return $categoryPath['full'];
            }
            return $categoryPath['plain'];
        }
        return false;
    }

    /**
     * @return string|null
     */
    public function getCategoryName()
    {
        $_category = $this->_layer->getCurrentCategory();
        if ($_category) {
            return $this->_getCategoryPath->execute($_category);
        }
        return '';
    }

    /**
     * @return array|string|string[]|null
     */
    public function getRemarketingCategories()
    {
        $_category = $this->_layer->getCurrentCategory();
        if ($_category) {
            return $this->_getCategoryPath->execute($_category, true);
        }
        return [
            'full' => '',
            'plain' => ''
        ];
    }
}
