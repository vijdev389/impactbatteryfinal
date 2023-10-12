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

    return function (widget) {
        $.widget('mage.catalogAddToCart', widget, {
            gaAddToCart: function($){
                $.ajax({
                    url: url.build('gtmpro/index/addtocart'),
                    type: 'get',
                    dataType: 'json',
                    success: function(product) {
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
                            success: function(response) {
                            }
                        });
                    }
                });

            },
            ajaxSubmit: function(form) {
                var self = this, formData;
                $(self.options.minicartSelector).trigger('contentLoading');
                self.disableAddToCartButton(form);
                formData = new FormData(form[0]);

                $.ajax({
                    url: form.attr('action'),
                    data: formData,
                    type: 'post',
                    dataType: 'json',
                    contentType: false,
                    processData: false,

                    beforeSend: function() {
                        if (self.isLoaderEnabled()) {
                            $('body').trigger(self.options.processStart);
                        }
                    },
                    success: function(res) {
                        if (self.isLoaderEnabled()) {
                            $('body').trigger(self.options.processStop);
                        }

                        if (res.backUrl) {
                            window.location = res.backUrl;
                            return;
                        }
                        if (res.messages) {
                            $(self.options.messagesSelector).html(res.messages);
                        }
                        if (res.minicart) {
                            $(self.options.minicartSelector).replaceWith(res.minicart);
                            $(self.options.minicartSelector).trigger('contentUpdated');
                        }
                        if (res.product && res.product.statusText) {
                            $(self.options.productStatusSelector)
                                .removeClass('available')
                                .addClass('unavailable')
                                .find('span')
                                .html(res.product.statusText);
                        }
                        self.enableAddToCartButton(form);
                        self.gaAddToCart($);
                    }

                });
            }
        });
        return $.mage.catalogAddToCart;
    };
});
