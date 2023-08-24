<?php
/**
 * Google Tag Manager Data Helper
 *
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GoogleTagManagerPro\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Scommerce\Core\Helper\Data as CoreData;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProduct;
use Scommerce\GoogleTagManagerPro\Model\Session;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Admin configuration paths
     *
     */
    const XML_PATH_ENABLED = 'googletagmanagerpro/general/active';
    const XML_PATH_LICENSE_KEY = 'googletagmanagerpro/general/license_key';
    const XML_PATH_ACCOUNT_ID = 'googletagmanagerpro/general/account_id';
    const XML_PATH_BASE = 'googletagmanagerpro/general/base';
    const XML_PATH_ENHANCED_ECOMMERCE = 'googletagmanagerpro/general/enhanced_ecommerce_enabled';
    const XML_PATH_CAT_AJAX_ENABLED = 'googletagmanagerpro/general/category_ajax_enabled';
    const XML_PATH_ENHANCED_SIOS = 'googletagmanagerpro/general/send_impression_on_scroll';
    const XML_PATH_ENHANCED_PIC_TEXT = 'googletagmanagerpro/general/product_item_class';
    const XML_PATH_ENHANCED_BRAND_DROPDOWN = 'googletagmanagerpro/general/brand_dropdown';
    const XML_PATH_ENHANCED_BRAND_TEXT = 'googletagmanagerpro/general/brand_text';
    const XML_PATH_ENABLE_DYNAMIC = 'googletagmanagerpro/general/enable_dynamic';
    const XML_PATH_ENABLE_OTHER_SITES = 'googletagmanagerpro/general/enable_other_sites';
    const XML_PATH_ATTRIBUTE_KEY = 'googletagmanagerpro/general/attribute_key';
    const XML_PATH_AJAX_ENABLED = 'googletagmanagerpro/general/ajax_enabled';
    const XML_PATH_GDPR_COOKIE_ENABLED = 'googletagmanagerpro/general/gdpr_cookie_enabled';
    const XML_PATH_GDPR_FORCE_DECLINE = 'googletagmanagerpro/general/force_decline';
    const XML_PATH_GDPR_COOKIE_KEY = 'googletagmanagerpro/general/gdpr_cookie_key';
    const XML_PATH_SEND_ADMIN_ORDERS = 'googletagmanagerpro/general/send_admin_orders';
    const XML_PATH_ADMIN_SOURCE = 'googletagmanagerpro/general/admin_source';
    const XML_PATH_ADMIN_MEDIUM = 'googletagmanagerpro/general/admin_medium';
    const XML_PATH_TOTAL_INCLUDE_VAT = 'googletagmanagerpro/general/order_total_include_vat';
    const XML_PATH_SEND_PARENT_SKU = 'googletagmanagerpro/general/send_parent_sku';
    const XML_PATH_SEND_CATEGORY_PATH = 'googletagmanagerpro/general/send_parent_category';
    const XML_PATH_SCROLL_THRESHOLD = 'googletagmanagerpro/general/scroll_threshold';

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var CoreData
     */
    protected $_data;

    /**
     * @var Product
     */
    protected $_productHelper;

    /**
     * @var Generic
     */
    protected $_objectManager;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var ConfigurableProduct
     */
    protected $_configurableProduct;

    /** @var ConfigInterface */
    protected $_sessionConfig;

    /** @var CategoryRepositoryInterface  */
    protected $_categoryRepository;

    /**
     * @var Session
     */
    protected $_gtmSession;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CoreData $data
     * @param Product $productHelper
     * @param StoreManagerInterface $storeManager
     * @param CookieManagerInterface $cookieManager
     * @param ObjectManagerInterface $objectManager
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigurableProduct $configurableProduct
     * @param ConfigInterface $sessionConfig
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CoreData $data,
        Product $productHelper,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        ObjectManagerInterface $objectManager,
        ProductRepositoryInterface $productRepository,
        ConfigurableProduct $configurableProduct,
        ConfigInterface $sessionConfig,
        CategoryRepositoryInterface $categoryRepository,
        Session $gtmSession
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_data = $data;
        $this->_productRepository = $productRepository;
        $this->_productHelper = $productHelper;
        $this->_objectManager = $objectManager;
        $this->_cookieManager = $cookieManager;
        $this->_storeManager = $storeManager;
        $this->_configurableProduct = $configurableProduct;
        $this->_sessionConfig = $sessionConfig;
        $this->_categoryRepository = $categoryRepository;
        $this->_gtmSession = $gtmSession;
    }

    /**
     * returns whether module is enabled or not
     *
     * @param null $storeId
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) && $this->isLicenseValid() && $this->getAccountId();
    }

    /**
     * returns account id
     * @param null $storeId
     * @return string
     */
    public function getAccountId($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ACCOUNT_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * returns current store currency code
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * returns formatted produce price
     * @param Magento/Catalog/Model/Product
     * @return float
     */
    public function productPrice($product)
    {
        $price = 0;
        if ($this->_productHelper->getFinalPrice($product) > 0) {
            $price = $this->_productHelper->getFinalPrice($product);
        } elseif ($this->_productHelper->getPrice($product) > 0) {
            $price = $this->_productHelper->getPrice($product);
        }
        return number_format($price, 2);
    }

    /**
     * @return string
     */
    public function getCategoryFromCookie()
    {
        return (string)$this->_cookieManager->getCookie('googlecategory');
    }

    /**
     * returns product category name
     *
     * @param $_product
     * @return string
     */
    public function getProductCategoryName($_product)
    {
        $primaryCategory = $_product->getResource()->getAttributeRawValue(
            $_product->getId(),
            'product_primary_category',
            $_product->getStore()
        );
        if ($primaryCategory) {
            $cat = $this->getCategory($primaryCategory);
            if ($cat != null) {
                return $this->getCategoryPath($cat);
            }
        }

        $_cats = $_product->getCategoryIds();
        $_categoryId = array_pop($_cats);
        if (!isset($_categoryId) && $_product->getTypeId()== \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $_parentProduct = $this->parentProduct($_product->getId());
            if ($_parentProduct) {
                $_cats = $_parentProduct->getCategoryIds();
                $_categoryId = array_pop($_cats);
            }
        }

        $_cat = $this->_objectManager->create('\Magento\Catalog\Model\Category')->load($_categoryId);
        return $this->getCategoryPath($_cat);
    }

    /**
     * @param $childProductId
     * @return mixed
     */
    public function parentProduct($childProductId)
    {
        $parentProduct = $this->_configurableProduct->getParentIdsByChild($childProductId);
        if (isset($parentProduct[0])){
            $_parentProduct = $this->_productRepository->getById($parentProduct[0]);
            if ($_parentProduct) {
                return $_parentProduct;
            }
        }
    }

    /**
     * returns category name
     * @param $quoteItem Item
     * @return string
     */
    public function getQuoteCategoryName($quoteItem)
    {
        if ($_catName = $quoteItem->getGoogleCategory()) {
            return $_catName;
        }

        $_product = $quoteItem->getProduct();

        if (!($_product)) $_product = $this->_productRepository->getById($quoteItem->getProductId());

        return $this->getProductCategoryName($_product);
    }

    /**
     * returns product sku
     * @param $quoteItem \Magento\Quote\Model\Quote\Item
     * @return string
     */
    public function getParentSKU($quoteItem)
    {
        if ($this->sendParentSKU()){

            if ($quoteItem->getParentItemId()){
                $_product = $this->parentProduct($quoteItem->getProductId());
            }
            else {
                //DONT CHANGE OBJECT MANAGER AS IT DOESN'T GET UPDATED SKU
                $_productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
                $_product = $_productRepository->getById($quoteItem->getProductId());
            }

            if ($_product) {
                return $_product->getSku();
            }
        }
        return $quoteItem->getSku();
    }

    /**
     * returns whether enhanced ecommerce is enabled or not
     * @param null $storeId
     * @return string
     */
    public function isEnhancedEcommerceEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENHANCED_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * returns whether send impression on scroll is enabled or not
     * @return boolean
     */
    public function isSIOSEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENHANCED_SIOS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns scroll threshold
     * @return boolean
     */
    public function getScrollThreshold()
    {
        $threshold = $this->scopeConfig->getValue(
            self::XML_PATH_SCROLL_THRESHOLD,
            ScopeInterface::SCOPE_STORE
        );
        if (!$threshold) {
            $threshold = 50;
        }
        return $threshold;
    }

    /**
     * returns if category ajax is enabled or not
     * @return boolean
     */
    public function isCategoryAjaxEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CAT_AJAX_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns product item class static text
     * @return string
     */
    public function getPICText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENHANCED_PIC_TEXT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns whether base order data is enabled or not
     * @return boolean
     */
    public function sendBaseData()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_BASE,
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * returns attribute id of brand
     * @return string
     */
    public function getBrandDropdown()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENHANCED_BRAND_DROPDOWN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns brand static text
     * @return string
     */
    public function getBrandText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENHANCED_BRAND_TEXT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns brand value using product or text
     * @param $product CatalogProduct
     * @return int
     * @throws NoSuchEntityException
     */
    public function getBrand($product)
    {
        $_product = $product;
        if ($attribute = $this->getBrandDropdown()) {
            $data = $_product->getAttributeText($attribute);
            if (is_array($data)) $data = end($data);
            if (strlen($data) == 0) {
                $data = $_product->getData($attribute);
            }
            if (!isset($data)) {
                return null;
            }
            return $data;
        }
        return $this->getBrandText();
    }

    /**
     * checks to see if the extension is enabled for advanced tagging in admin
     *
     * @return boolean
     */
    public function getDynamicRemarketingEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_DYNAMIC,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * checks to see if the other site variable is enabled or not
     *
     * @return boolean
     */
    public function isOtherSiteEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_OTHER_SITES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns product attribute key
     *
     * @return string
     */
    public function getProductAttributeKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ATTRIBUTE_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve domain url without www or subdomain
     *
     * @return string
     */
    public function getDomain()
    {
        $host = $this->_request->getHttpHost();
        if (substr_count($host,'.')>1){
            return substr($host,strpos($host,'.')+1);
        }
        return $host;
    }

    /**
     * Retrieve page as display mode
     *
     * @return string
     */
    public function getCMDisplayMode()
    {
        return 'PAGE';
    }

    /**
     * returns whether ajax add to basket is enabled or not
     * @return string
     */
    public function isAjaxEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_AJAX_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns whether GDPR cookie check is enabled or not
     *
     * @return boolean
     */
    public function isGDPRCookieEnabled() {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GDPR_COOKIE_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns force decline is on or not
     *
     * @return boolean
     */
    public function isGDPRCookieForceDeclined() {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GDPR_FORCE_DECLINE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get cookie key to check accepted cookie policy
     *
     * @return string
     */
    public function getCookieKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GDPR_COOKIE_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if has cookie with accepted cookie policy
     *
     * @return bool
     */
    protected function hasCookie()
    {
        $cookieKey = $this->getCookieKey();
        if (!$this->isGDPRCookieEnabled() || strlen($cookieKey)==0) return true;
        $cookie = (string)$this->_cookieManager->getCookie($cookieKey);
        if (!$this->isGDPRCookieForceDeclined()){
            if ($cookie=="0"){
                return false;
            }
            else{
                return true;
            }
        }
        else{
            if ($cookie=="1"){
                return true;
            }
            else{
                return false;
            }
        }
    }

    /**
     * returns if sending admin orders enabled
     *
     * @param null $storeId
     * @return boolean
     */
    public function getSendAdminOrdersEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_ADMIN_ORDERS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * return admin source
     *
     * @param null $storeId
     * @return string
     */
    public function getAdminSource($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_SOURCE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * return admin medium
     *
     * @param null $storeId
     * @return string
     */
    public function getAdminMedium($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_MEDIUM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isOrderTotalIncludedVAT($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_TOTAL_INCLUDE_VAT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function sendParentSKU($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_PARENT_SKU,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function sendCategoryPath($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_CATEGORY_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $category
     * @throws NoSuchEntityException
     */
    public function getCategoryPath($category)
    {
        if (!($category instanceof Category)) {
            $category = $this->getCategory($category);
        }
        if (!$this->sendCategoryPath()) {
            return $category->getName();
        }
        $result[] = $category->getName();
        $parent = $category->getParentCategory();
        while ($parent && $parent->getLevel() > 1) {
            if ($parent->getLevel() == 1) {
                break;
            }
            $result[] = $parent->getName();
            $parent = $parent->getParentCategory();
        }
        $result = array_reverse($result);
        return implode('->', $result);
    }

    /**
     * @param $categoryId
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getCategory($categoryId)
    {
        try {
            return $this->_categoryRepository->get($categoryId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * returns license key administration configuration option
     *
     * @return string
     */
    public function getLicenseKey(){
        return $this->scopeConfig->getValue(
            self::XML_PATH_LICENSE_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns whether license key is valid or not
     *
     * @return bool
     */
    public function isLicenseValid(){
        $sku = strtolower(str_replace('\\Helper\\Data','',str_replace('Scommerce\\','',get_class($this))));
        return $this->_data->isLicenseValid($this->getLicenseKey(),$sku);
    }
}
