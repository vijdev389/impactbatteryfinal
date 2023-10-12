define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('trackingBinds', {
        component: 'trackingBinds',
        options: {
            pageType: 'other',
            sendFullList: false,
            sendDefaultList: false,
            defaultList: false,
            currency: '',
            isGuest: false,
            cartData: null,
            cartDataHasItems: false,
            productOutBasket: null,
            productToWishlist: null,
            skipPageReady: false,
            tracking: null,
            url: null
        },
        scUpdating: false,
        scClicked: false,
        create: function () {
            let tracking = this.options.tracking;
            let url = this.options.url;
            let self = this;

            this._gaAddToCart = function () {
                if (self.scClicked === false) return;
                if (self.scUpdating === true) return;
                self.scUpdating = true;
                $.ajax({
                    url: url.build('sctracking/index/addtocart'),
                    type: 'get',
                    dataType: 'json',
                    success: function(product) {
                        if (product == null) return;
                        for (let i = 0; i < product.length; i++) {
                            product[i].list = tracking.getProductImpression(product[i].allSkus);
                        }
                        tracking.setAddToCart(product);
                        $.ajax({
                            url: url.build('sctracking/index/unsaddtocart'),
                            type: 'POST',
                            data: {product},
                            dataType: 'json'
                        });
                    },
                    complete: function() {
                        self.scUpdating = false;
                        self.scClicked = false;
                    }
                });
            };
            this.addToCart = function () {
                self.scClicked = true;
                self._gaAddToCart();
            };
            $(document).on('ajax:addToCart', this.addToCart);

            this.linkClick = function () {
                let href = $(this).attr('href');
                let product = tracking.findProductByUrl(href);
                if (product !== undefined && product != false) {
                    tracking.setProductImpression(product.id, product.list);
                    tracking.fire('item_click', product);
                }
            };
            this.clickToWishlist = function () {
                var wishlistData = $(this).data('post');
                var itemId = wishlistData.data.product;
                setTimeout(function () {
                    $.ajax({
                        url: url.build('sctracking/index/addtowishlist'),
                        type: 'post',
                        dataType: 'json',
                        data: {itemId: itemId}
                    }).success(function (product) {
                        if (product == null) return;
                        tracking.setAddToWishlist(product);
                    });
                }, 1000);
            };

            tracking.setPageType(this.options.pageType);
            tracking.setSendFullList(this.options.sendFullList);
            tracking.setSendDefaultList(this.options.sendDefaultList);
            tracking.setDefaultList(this.options.defaultList);
            tracking.setCurrency(this.options.currency);
            tracking.setData('isGuest', this.options.isGuest);

            if (this.options.cartData) {
                let cartData = this.options.cartData;
                if (this.options.cartDataHasItems) {
                    for (let i = 0; i < cartData.length; i++) {
                        let impression = tracking.getProductImpression(cartData[i].allSkus);
                        cartData[i].list = impression;
                        tracking.sendQuoteImpression(cartData[i]['_realProductId'], impression);
                    }
                }
                tracking.setAddToCart(this.options.cartData);
            }

            if (this.options.productOutBasket) {
                tracking.setRemoveFromCart(this.options.productOutBasket);
            }

            if (this.options.productToWishlist) {
                let wishlistData = this.options.productToWishlist;
                wishlistData.item.list = tracking.getProductImpression(wishlistData.item.allSkus);
                tracking.setAddToWishlist(wishlistData);
            } else {
                $('a.towishlist').on('click', this.clickToWishlist);
            }

            $(document).on('click', 'a', this.linkClick);

            let promotions = [];
            let intCtr = 0;
            $(document).on('breeze:load', function () {
                $('a[data-promotion]').each(function () {
                    if ($(this).data("id") != undefined) {
                        $(this).addClass('sc-in-view-promo' + $(this).data("id"));
                    }
                })
            });

            $(window).bind("scroll load", function () {
                $('a[data-promotion]').each(function () {
                    let selector = 'sc-in-view-promo' + $(this).data("id");
                    let promoEl = $('.' + selector);
                    if (promoEl.isInViewport() && !promoEl.hasClass('sc-promo-sent')) {
                        promotions = [];
                        let id = $(this).data("id");
                        let name = $(this).data("name");
                        let creative = $(this).data("creative");
                        let position = $(this).data("position");
                        let slot = $(this).data("slot");
                        let promotion = {
                            'id': id,                         // Name or ID is required.
                            'name': name,
                            'creative': creative,
                            'position': position,
                            'slot': slot
                        };
                        promotions.push(promotion);
                        $(this).addClass('sc-promo-sent');
                        intCtr++;

                        $(this).click(function (e) {
                            promotion.href = $(this).attr('href');
                            tracking.fire('promo_click', promotion);
                        });

                        if (intCtr > 0) {
                            tracking.setPromotions(promotions);
                            intCtr = 0;
                        }
                    }
                });
            });

            $.fn.isInViewport = function() {
                if ($(this).offset() != undefined) {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();

                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();

                    return elementBottom > viewportTop && elementTop < viewportBottom;
                }
                return false;
            };

            if (this.options.skipPageReady) {
                this._on('breeze:load', function() {
                    tracking.fire('page_ready', tracking.getPageType()?.toLowerCase() || "");
                });
            }
            $(document).trigger('scTracking:done');
        },
        destroy: function () {
            $(document).off('ajax:addToCart', this.addToCart);
            $('a.towishlist').off('click', this.clickToWishlist);
            $(document).off('click', 'a', this.linkClick);
        }
    });
});
