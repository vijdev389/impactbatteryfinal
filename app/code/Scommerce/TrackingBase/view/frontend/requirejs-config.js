/**
 * Copyright © 2021 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/sidebar': {
                'Scommerce_TrackingBase/js/sidebar-mixin': true
            },
        }
    },
    map: {
        '*': {
			scTrackingData: 'Scommerce_TrackingBase/js/tracking-data',
            jqueryViewport: 'Scommerce_TrackingBase/js/inviewport_jquery'
        }
    }
};
