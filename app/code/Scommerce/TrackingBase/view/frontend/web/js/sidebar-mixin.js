/**
 * Copyright © 2021 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global confirm:true*/
define([
    'jquery',
    'mage/url',
    'scTrackingData'
], function ($, url, Tracking) {
    "use_strict";

    let tracking = Tracking();

    return function (widget) {
        $.widget('mage.sidebar', widget, {
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
            /**
             * Update content after item remove
             *
             * @param elem
             * @param response
             * @private
             */
            _removeItemAfter: function (elem, response) {
                this._gaRemoveFromCart($);
                this._super(elem, response);
            },

            /**
             * Calculate height of minicart list
             *
             * @private
             */
            _calcHeight: function () {
                var self = this,
                    height = 0,
                    counter = this.options.minicart.maxItemsVisible,
                    target = $(this.options.minicart.list),
                    outerHeight;

                self.scrollHeight = 0;
                target.children().each(function () {

                    if ($(this).find('.options').length > 0) {
                        $(this).collapsible();
                    }
                    outerHeight = $(this).outerHeight();

                    if (counter-- > 0) {
                        height += outerHeight;
                    }
                    self.scrollHeight += outerHeight;
                });
                if (height > 0) {
                    target.parent().height(height);
                }
            }
        });
        return $.mage.sidebar;
    }
});
