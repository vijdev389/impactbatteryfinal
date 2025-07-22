<?php
/**
 * Google Tag Manager Data Helper
 *
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GoogleTagManagerPro\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Scommerce\TrackingBase\Helper\Data as TrackingBaseHelper;
use Scommerce\Core\Helper\Data as CoreData;
use Scommerce\CspHelper\Helper\CspHelper;

/**
 * Class Data
 * @package Scommerce\GoogleTagManagerPro\Helper
 */
class Data extends TrackingBaseHelper
{
    /**
     * Admin configuration paths
     *
     */
    const XML_PATH_ENABLED              = 'googletagmanagerpro/general/active';
    const XML_PATH_LICENSE_KEY          = 'googletagmanagerpro/general/license_key';
    const XML_PATH_ACCOUNT_ID           = 'googletagmanagerpro/general/account_id';
    const XML_PATH_ENABLE_DYNAMIC       = 'googletagmanagerpro/general/enable_dynamic';
    const XML_PATH_ENABLE_OTHER_SITES   = 'googletagmanagerpro/general/enable_other_sites';
    const XML_PATH_ECOMM_CATEGORY_PATH  = 'googletagmanagerpro/general/ecomm_category_path';
    const XML_PATH_GDPR_COOKIE_ENABLED  = 'googletagmanagerpro/general/gdpr_cookie_enabled';
    const XML_PATH_GDPR_FORCE_DECLINE   = 'googletagmanagerpro/general/force_decline';
    const XML_PATH_GDPR_COOKIE_KEY      = 'googletagmanagerpro/general/gdpr_cookie_key';
    const XML_PATH_ADD_GA4_EVENTS       = 'googletagmanagerpro/general/add_ga_events';

    const XML_PATH_COOKIES_ENABLED          = 'googletagmanagerpro/cookies/enabled';
    const XML_PATH_COOKIES_CONFIGURATION    = 'googletagmanagerpro/cookies/configuration';
    const XML_PATH_COOKIES_LIFETIME         = 'googletagmanagerpro/cookies/lifetime';

    /**
     * @var CoreData
     */
    protected $_data;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var SerializerInterface
     */
    protected $_serializer;

    protected $cspHelper;

    /**
     * Data constructor.
     * @param Context $context
     * @param CoreData $data
     * @param StoreManagerInterface $storeManager
     * @param CookieManagerInterface $cookieManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Template $block
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        CoreData $data,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        PriceCurrencyInterface $priceCurrency,
        Template $block,
        SerializerInterface $serializer,
        CspHelper $cspHelper
    ) {
        parent::__construct($context, $storeManager, $priceCurrency, $block, $serializer, $cspHelper);
        $this->_data = $data;
        $this->_cookieManager = $cookieManager;
    }

    /**
     * Returns whether module is enabled or not
     *
     * @param null $storeId
     * @return boolean
     */
    public function isEnabled($storeId = null): bool
    {
        return parent::isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) && $this->isLicenseValid() && $this->getAccountId();
    }

    public function getNonce()
    {
        return parent::getNonce();
    }

    /**
     * Returns account id
     *
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
     * Checks to see if the other site variable is enabled or not
     *
     * @param null $storeId
     * @return boolean
     */
    public function isOtherSiteEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_OTHER_SITES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Checks if dynamic remarketing enabled
     *
     * @param null $storeId
     * @return boolean
     */
    public function getDynamicRemarketingEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_DYNAMIC,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Checks ecom category path
     *
     * @param null $storeId
     * @return boolean
     */
    public function sendEcommCategoryPath($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ECOMM_CATEGORY_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
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
     * Returns whether GDPR cookie check is enabled or not
     *
     * @param null $storeId
     * @return boolean
     */
    public function isGDPRCookieEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GDPR_COOKIE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }


    /**
     * Returns force decline is on or not
     *
     * @return boolean
     */
    public function isGDPRCookieForceDeclined()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GDPR_FORCE_DECLINE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get cookie key to check accepted cookie policy
     *
     * @param null $storeId
     * @return string
     */
    public function getCookieKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GDPR_COOKIE_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
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
        if (!$this->isGDPRCookieEnabled() || strlen($cookieKey) == 0) return true;
        $cookie = (string)$this->_cookieManager->getCookie($cookieKey);
        if (!$this->isGDPRCookieForceDeclined()) {
            if ($cookie == "0") {
                return false;
            } else {
                return true;
            }
        } else {
            if ($cookie == "1") {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Returns flag to add GA4 events
     *
     * @param null $storeId
     * @return bool
     */
    public function getAddGa4Events($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADD_GA4_EVENTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns flag to add GA4 events
     *
     * @param null $storeId
     * @return bool
     */
    public function useUaEvents()
    {
        return false;
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function getCookiesEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COOKIES_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return array|bool|float|int|string|null
     */
    public function getCookiesConfiguration($storeId = null)
    {
        $raw = $this->scopeConfig->getValue(
            self::XML_PATH_COOKIES_CONFIGURATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (empty($raw)) {
            return [];
        }
        if (!$values = $this->_serializer->unserialize($raw)) {
            return [];
        }
        return $values;
    }


    /**
     * @param null $storeId
     * @return bool
     */
    public function getCookiesLifetime($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COOKIES_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns license key administration configuration option
     *
     * @param null $storeId
     * @return string
     */
    public function getLicenseKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LICENSE_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns whether license key is valid or not
     *
     * @return bool
     */
    public function isLicenseValid()
    {
        $sku = strtolower(str_replace('\\Helper\\Data','',str_replace('Scommerce\\','',get_class($this))));
        return $this->_data->isLicenseValid($this->getLicenseKey(), $sku);
    }
}
