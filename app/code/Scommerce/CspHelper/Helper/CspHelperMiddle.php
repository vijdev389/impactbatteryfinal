<?php

namespace Scommerce\CspHelper\Helper;

if (class_exists('Magento\Csp\Helper\CspNonceProvider')) {
    class CspHelperMiddle extends \Magento\Csp\Helper\CspNonceProvider { }
} else {
    class CspHelperMiddle {
        public function generateNonce() {
            return false;
        }
    }
}
