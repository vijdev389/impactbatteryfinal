<?php

namespace Scommerce\GoogleTagManagerPro\Model;

use Magento\Framework\App\Request\Http;
use Scommerce\GoogleTagManagerPro\Helper\Data;

/**
 * Class Cookies
 */
class Cookies
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Cookies constructor.
     * @param Http $request
     * @param Data $helper
     */
    public function __construct(
        Http $request,
        Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getCookiesToSet(): array
    {
        $result = [];
        $cookiesToProceed = $this->helper->getCookiesConfiguration();

        foreach ($cookiesToProceed as $cookieConfig) {
            $param = $this->request->getParam($cookieConfig['query_param']);
            if ($param) {
                $value = str_replace('{{value}}', $param, $cookieConfig['cookie_value']);
                $result[$cookieConfig['cookie_name']] = $value;
            }
        }
        return $result;
    }
}
