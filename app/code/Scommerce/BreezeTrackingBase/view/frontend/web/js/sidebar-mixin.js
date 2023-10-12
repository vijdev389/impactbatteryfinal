/*jshint browser:true jquery:true*/
/*global confirm:true*/
define([
    'jquery',
    'mage/url',
    'scTrackingData'
], function ($, url, Tracking) {
    "use_strict";

    let tracking = Tracking;

    $.mixin('sidebar', {
        _gaRemoveFromCart: function ($) {
            $.ajax({
                url: url.build('sctracking/index/removefromcart'),
                type: 'get',
                dataType: 'json',
                success: function (product) {
                    tracking.setRemoveFromCart(product);
                    $.ajax({
                        url: url.build('sctracking/index/unsremovefromcart'),
                        type: 'get',
                        success: function (response) { }
                    });
                }
            });
        },
        _removeItemAfter: function (original, elem) {
            this._gaRemoveFromCart($);
            original(elem);
        },
    });
});
