/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Scommerce_GoogleTagManagerPro/js/add-to-cart-mixin': true
            },
            'Magento_Checkout/js/sidebar': {
                'Scommerce_GoogleTagManagerPro/js/sidebar-mixin': true
            },
        }
    },
    map: {
        '*': {
			jqueryviewport:         'Scommerce_GoogleTagManagerPro/js/inviewport_jquery'
        }
    }
};
