/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate',
	'mage/url'
], function($, $t, url) {
    "use strict";

    $(document).on('ajax:addToCart', function (e, data) {
        $.ajax({
            url: url.build('gtmpro/index/addtocart'),
            type: 'get',
            dataType: 'json',
            success: function (product) {
                var list = '';
                var category = '';
                if (product != undefined) {
                    var res = [];
                    for (var i = 0; i < product.length; i++) {
                        var pr = product[i];
                        var impression = scGetProductImpression(pr.allSkus);
                        if (impression != '') {
                            list = impression.list;
                            category = impression.category;
                        }
                        if (list == '') {
                            if (pr.list == undefined || pr.list == '') {
                                list = 'Category - ' + pr.category;
                            } else {
                                list = pr.list;
                            }
                        }
                        if (category == '') {
                            category = pr.category;
                        }
                        delete pr.allSkus;
                        pr.list = list;
                        pr.category = category;
                        res.push(pr);
                    }
                    dataLayer.push({
                        'event': 'addToCart',
                        'ecommerce': {
                            'currencyCode': product.currency,
                            'add': {                                // 'add' actionFieldObject measures.
                                'products': res
                            }
                        }
                    });
                }
                $.ajax({
                    url: url.build('gtmpro/index/unsaddtocart'),
                    type: 'get',
                    success: function (response) {
                    }
                });
            }
        });
    });

    return function (widget) {
        $.widget('mage.catalogAddToCart', widget, {

        });
        return $.mage.catalogAddToCart;
    };
});
