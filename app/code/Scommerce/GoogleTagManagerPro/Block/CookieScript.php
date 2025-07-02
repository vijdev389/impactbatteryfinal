<?php

namespace Scommerce\GoogleTagManagerPro\Block;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Scommerce\GoogleTagManagerPro\Helper\Data;
use Scommerce\GoogleTagManagerPro\Model\Cookies;

/**
 * Class CookieScript
 */
class CookieScript implements ArgumentInterface
{
    protected $helper;

    protected $cookies;

    /**
     * CookieScript constructor.
     * @param Data $helper
     * @param Cookies $cookies
     */
    public function __construct(
        Data $helper,
        Cookies $cookies
    ) {
        $this->helper = $helper;
        $this->cookies = $cookies;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * @return array
     */
    public function cookiesToSet()
    {
        return $this->cookies->getCookiesToSet();
    }

    /**
     * @return bool
     */
    public function cookieLifeTime()
    {
        return $this->helper->getCookiesLifetime();
    }
}
