<?php

namespace Scommerce\CspHelper\Helper;

use Magento\Framework\App\Helper\Context;

class CspHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $cspHelperMiddle;

    public function __construct(
        Context $context,
        \Scommerce\CspHelper\Helper\CspHelperMiddle $cspHelperMiddle
    ) {
        parent::__construct($context);
        $this->cspHelperMiddle = $cspHelperMiddle;
    }

    public function generateNonce(): string
    {
        $nonce = $this->cspHelperMiddle->generateNonce();
        if ($nonce) {
            return " nonce=\"" . $nonce . "\"";
        } else {
            return '';
        }
    }
}
