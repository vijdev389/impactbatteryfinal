define([
    'jquery',
    'mage/translate',
    'jquery/validate'
], function ($) {
    'use strict';

    return function (productCodeValidator) {
        $.validator.addMethod(
            'validate-product-sku',
            function (value) {
                var format = /[!@#$%^&*()+\=\[\]{};:"\\|,.<>\/?]+/;
                if(format.test(value)){
                    return false;
                } else {
                  return true;
                }
            },
            $.mage.__('Special character is not allowed.')
        );

        return productCodeValidator;
    };
});
