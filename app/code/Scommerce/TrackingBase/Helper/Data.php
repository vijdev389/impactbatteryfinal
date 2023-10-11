<?php
/**
 * Scommerce TrackingBase Data Helper
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Data
 * @package Scommerce\TrackingBase\Helper
 */
class Data extends AbstractHelper
{
    const WISHLIST_REGISTRY = 'scWishlistAction';

    /** Admin configuration paths */
    const XML_PATH_ENABLED                  = 'scommerce_trackingbase/general/active';
    const XML_PATH_BASE                     = 'scommerce_trackingbase/general/base';
    const XML_PATH_ENHANCED_ECOMMERCE       = 'scommerce_trackingbase/general/enhanced_ecommerce_enabled';
    const XML_PATH_ENHANCED_CONVERSION       = 'scommerce_trackingbase/general/enhanced_conversion_enabled';
    const XML_PATH_PRODUCT_ID_ATTRIBUTE     = 'scommerce_trackingbase/general/attribute_key';
    const XML_PATH_CATEGORY_ATTRIBUTE       = 'scommerce_trackingbase/general/category_attribute';
    const XML_PATH_IS_CATEGORY_ID_ATTRIBUTE = 'scommerce_trackingbase/general/is_category_id';
    const XML_PATH_ENHANCED_BRAND_DROPDOWN  = 'scommerce_trackingbase/general/brand_dropdown';
    const XML_PATH_ENHANCED_BRAND_TEXT      = 'scommerce_trackingbase/general/brand_text';
    const XML_PATH_PRICE_INCLUDE_TAX        = 'scommerce_trackingbase/general/price_including_tax';
    const XML_PATH_TOTAL_INCLUDE_VAT        = 'scommerce_trackingbase/general/order_total_include_vat';
    const XML_PATH_SEND_PARENT_SKU          = 'scommerce_trackingbase/general/send_parent_sku';
    const XML_PATH_SEND_CATEGORY_PATH       = 'scommerce_trackingbase/general/send_parent_category';
    const XML_PATH_DEFAULT_LIST             = 'scommerce_trackingbase/general/default_list';
    const XML_PATH_FULL_LIST                = 'scommerce_trackingbase/general/full_list_name';
    const XML_PATH_SEND_DEFAULT_LIST        = 'scommerce_trackingbase/general/send_default_list';
    const XML_PATH_SEND_ADMIN_ORDERS        = 'scommerce_trackingbase/general/send_admin_orders';
    const XML_PATH_ADMIN_SOURCE             = 'scommerce_trackingbase/general/admin_source';
    const XML_PATH_ADMIN_MEDIUM             = 'scommerce_trackingbase/general/admin_medium';
    const XML_PATH_ENHANCED_SIOS            = 'scommerce_trackingbase/general/send_impression_on_scroll';
    const XML_PATH_ENHANCED_PIC_TEXT        = 'scommerce_trackingbase/general/product_item_class';
    const XML_PATH_SCROLL_THRESHOLD         = 'scommerce_trackingbase/general/scroll_threshold';
    const XML_PATH_CAT_AJAX_ENABLED         = 'scommerce_trackingbase/general/category_ajax_enabled';
    const XML_PATH_AFFILIATION              = 'scommerce_trackingbase/general/affiliation';
    const XML_PATH_SLIDERTEXT               = 'scommerce_trackingbase/general/slider_text';

    const XML_PATH_CHECKOUT_ADD_CARRIER_TITLE   = 'scommerce_trackingbase/checkout/add_carrier_title';
    const XML_PATH_CHECKOUT_ADD_PAYMENT_TITLE   = 'scommerce_trackingbase/checkout/add_payment_title';
    const XML_PATH_CHECKOUT_STEPS               = 'scommerce_trackingbase/checkout/steps';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var Template
     */
    protected $_block;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Template $block
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        Template $block
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_priceCurrency = $priceCurrency;
        $this->_block = $block;
    }

    /**
     * Returns whether module is enabled or not
     *
     * @param null $storeId
     * @return boolean
     */
    public function isEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSliderText($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SLIDERTEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns whether enhanced ecommerce is enabled or not
     *
     * @param null $storeId
     * @return bool
     */
    public function isEnhancedEcommerceEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENHANCED_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns whether enhanced conversion is enabled or not
     *
     * @param null $storeId
     * @return bool
     */
    public function isEnhancedConversionEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENHANCED_CONVERSION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns whether base order data is enabled or not
     *
     * @param null $storeId
     * @return boolean
     */
    public function sendBaseData($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BASE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns attribute id of brand
     *
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
     * Returns brand static text
     *
     * @param null $storeId
     * @return string
     */
    public function getBrandText($storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENHANCED_BRAND_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns attribute id of brand
     *
     * @return string
     */
    public function getProductIdAttribute($storeId = null)
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_ID_ATTRIBUTE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (trim($value) == '') {
            return 'sku';
        }
        return $value;
    }

    /**
     * Returns category attribute
     *
     * @return int|string
     */
    public function getCategoryAttribute($storeId = null)
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_ATTRIBUTE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (trim($value) == '') {
            return 'product_primary_category';
        }
        return $value;
    }

    /**
     * Returns if category_attribute is ID of the category
     *
     * @return bool
     */
    public function getIsCategoryId($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_CATEGORY_ID_ATTRIBUTE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns if total should include VAT
     *
     * @param null $storeId
     * @return bool
     */
    public function isOrderTotalIncludedVAT($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_TOTAL_INCLUDE_VAT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns if product price should include tax
     *
     * @param null $storeId
     * @return bool
     */
    public function isPriceIncludedTax($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRICE_INCLUDE_TAX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns if parent SKU should be sent instead of child
     *
     * @param null $storeId
     * @return bool
     */
    public function sendParentSKU($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_PARENT_SKU,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns if category should include full path
     *
     * @param null $storeId
     * @return bool
     */
    public function sendCategoryPath($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_CATEGORY_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns default category list name
     *
     * @param null $storeId
     * @return string
     */
    public function getDefaultList($storeId = null): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_LIST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getSendFullList($storeId = null): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FULL_LIST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getSendDefaultList($storeId = null): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEND_DEFAULT_LIST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrency($storeId = null): string
    {
        if ($this->sendBaseData($storeId)) {
            return $this->_priceCurrency->getCurrency($this->_storeManager->getStore()->getId())->getCode();
        }
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @param $data
     * @return array|string
     */
    public function escapeJsQuote($data)
    {
        return $this->_block->escapeJsQuote($data);
    }

    /**
     * @param $quoteItem
     * @param $key
     * @param $value
     * @param string $checkType
     */
    public function setQuoteItemTrackingData($quoteItem, $key, $value, $checkType = 'children')
    {
        $trackingData = $quoteItem->getScTrackingData();
        if ($trackingData) {
            $trackingData = json_decode($trackingData, true);
        } else {
            $trackingData = [];
        }
        $trackingData[$key] = $value;
        $trackingData = json_encode($trackingData);
        $quoteItem->setScTrackingData($trackingData);
        if ($checkType == 'children') {
            if ($quoteItem->getHasChildren()) {
                $children = $quoteItem->getChildren();
                foreach ($children as $child) {
                    $child->setScTrackingData($trackingData);
                }
            }
        }
        if ($checkType == 'parent') {
            if ($quoteItem->getParentItem()) {
                $quoteItem->getParentItem()->setScTrackingData($trackingData);
            }
        }
    }

    /**
     * @param $quoteItem
     * @return mixed|string
     */
    public function getImpressionListFromQuoteItem($quoteItem)
    {
        $trackingData = $quoteItem->getScTrackingData();
        if ($trackingData) {
            $trackingData = json_decode($trackingData, true);
            if (isset($trackingData['list'])) {
                return $trackingData['list'];
            }
            return $this->getDefaultList();
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
     * Return admin medium
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
     * Returns whether send impression on scroll is enabled or not
     *
     * @param null $storeId
     * @return boolean
     */
    public function isSIOSEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENHANCED_SIOS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns scroll threshold
     *
     * @param null $storeId
     * @return boolean
     */
    public function getScrollThreshold($storeId = null)
    {
        $threshold = $this->scopeConfig->getValue(
            self::XML_PATH_SCROLL_THRESHOLD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$threshold) {
            $threshold = 50;
        }
        return $threshold;
    }

    /**
     * Returns product item class static text
     *
     * @param null $storeId
     * @return string
     */
    public function getPICText($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENHANCED_PIC_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns ajax is enabled for category
     *
     * @param null $storeId
     * @return boolean
     */
    public function isCategoryAjaxEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CAT_AJAX_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function getAddCarrierTitle($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CHECKOUT_ADD_CARRIER_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function getAddPaymentTitle($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CHECKOUT_ADD_PAYMENT_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getCheckoutStepsConfiguration($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CHECKOUT_STEPS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Affiliation
     *
     * @param null $storeId
     * @return string
     */
    public function getAffiliation($storeId = null)
    {
        $affiliation = $this->scopeConfig->getValue(
            self::XML_PATH_AFFILIATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$affiliation) {
            $affiliation = '';
        }
        return $affiliation;
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     */
    public function getValue($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     */
    public function isSetFlag($path, $storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
