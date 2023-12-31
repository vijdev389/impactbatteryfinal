/**
 * Copyright © 2021 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiClass',
    'mage/url'
], function ($, Class, url) {
    'use strict';

    if (window.scTrackingContainer !== undefined) {
        return window.scTrackingContainer;
    }

    window.scTrackingContainer = Class.extend({
        _internalData: {},

        /** @inheritdoc */
        initialize: function () {
            this._super();
            window.scTrackingData = window.scTrackingData || {
                productList: [],
                subscribers: {
                    'page_view': [],
                    'page_ready': [],
                    'promo_view': [],
                    'promo_click': [],
                    'view_list': [],
                    'item_click': [],
                    'view_item': [],
                    'add_to_cart': [],
                    'remove_from_cart': [],
                    'begin_checkout': [],
                    'checkout_step': [],
                    'checkout_option': [],
                    'purchase': [],
                    'listing_scroll': [],
                    'home_page': [],
                    'view_cart': [],
                    'add_to_wishlist': []
                },
                _fireEvents: false,
                _eventsQueue: [],
                _loggerEnabled: false
            };
            this._internalData = window.scTrackingData;

            return this;
        },

        _logData: function(data) {
            if (this._internalData._loggerEnabled === true) {
                try {
                    console.log(JSON.parse(JSON.stringify(data)));
                } catch (e) { }
            }
        },

        enableLogs: function() {
            this._internalData._loggerEnabled = true;
        },

        disableLogs: function() {
            this._internalData._loggerEnabled = false;
        },

        formatPrice: function(priceValue, asString) {
            let val = priceValue;
            if (typeof val === 'string')
            {
                val = val.replace(/,/g, '');
            }
            if (asString === undefined || asString !== true) {
                return parseFloat(parseFloat(val).toFixed(2));
            }
            return parseFloat(val).toFixed(2);
        },

        getListId: function(listName) {
            if ((listName == undefined || listName == "undefined" || listName == null || listName == "null")) {
                if (this.getSendDefaultList()) {
                    listName = this.getDefaultList();
                } else {
                    listName = '';
                }
            }
            return listName.trim().replace(/[^\w ]/g,' ').replace(/\s\s+/g, ' ').replace(/\s/g, '_').toLowerCase();
        },

        startEvents: function() {
            this._internalData._fireEvents = true;
            while (this._internalData._eventsQueue.length > 0) {
                let _event = this._internalData._eventsQueue.shift();
                this.fire(_event['eventName'], _event['data']);
            }
        },

        setData: function(key, data) {
            this._internalData[key] = data;
        },

        getData: function(key) {
            return this._internalData[key];
        },

        setPageType: function(pageType) {
            this._logData(pageType);
            this._internalData.pageType = pageType;
            this.fire('page_view', pageType);
        },

        setSendFullList: function(sendFullList) {
          this._logData(sendFullList);
          this._internalData.sendFullList = sendFullList;
        },

        getSendFullList: function () {
            return this.getData('sendFullList');
        },

        setSendDefaultList: function (sendDefaultList) {
            this._logData(sendDefaultList);
            this._internalData.sendDefaultList = sendDefaultList;
        },

        getSendDefaultList: function () {
          return this.getData('sendDefaultList');
        },

        setDefaultList: function(list) {
            this._logData(list);
            this._internalData.defaultList = list;
        },

        getDefaultList: function() {
            return this.getData('defaultList');
        },

        getPageType: function() {
            return this._internalData.pageType;
        },

        setCurrency: function(currency) {
            this._logData(currency);
            this._internalData.currency = currency;
        },

        getCurrency: function() {
            return this._internalData.currency;
        },

        setPromotions: function(promotions) {
            this._logData(promotions);
            this.setData('promotions', promotions);
            this.fire('promo_view', promotions);
        },

        getPromotions: function() {
            return this.getData('promotions');
        },

        setAddToCart: function(cartData) {
            this._logData(cartData);
            for (let i = 0; i < cartData.length; i++) {
                if (cartData[i].list === undefined) {
                    cartData[i].list = this.getProductImpression(cartData[i].allSkus);
                }
                cartData[i].price = this.formatPrice(cartData[i].price, true);
                cartData[i].qty = this.formatPrice(cartData[i].qty);
                delete cartData[i].allSkus;
            }
            this.setData('addToCart', cartData);
            this.fire('add_to_cart', cartData);
        },

        setAddToWishlist: function(data) {
            this._logData(data);
            this.setData('addToWishlist', data);
            this.fire('add_to_wishlist', data);
        },

        getAddedToCart: function() {
            return this.getData('addToCart');
        },

        setRemoveFromCart: function(cartData) {
            this._logData(cartData);
            cartData.price = this.formatPrice(cartData.price, true);
            this.setData('removeFromCart', cartData);
            this.fire('remove_from_cart', cartData);
            this._removeFromStorage(cartData);
        },

        _removeFromStorage: function(cartData) {
            let cd = this.getCartData();
            let newSkus = cartData.allSkus;
            if (cd !== undefined && newSkus !== undefined) {
                for (let i = 0; i < cd.length; i++) {
                    let item = cd[i];
                    let skus = item.allSkus;
                    if (skus !== undefined) {
                        let matchCount = 0;
                        for (let j = 0; j < skus.length; j++) {
                            for (let k = 0; k < newSkus.length; k++) {
                                if (newSkus[k] === skus[j]) {
                                    matchCount++;
                                }
                            }
                        }
                        if (matchCount === skus.length) {
                            cd.splice(i, 1);
                            break;
                        }
                    }
                }
            }
        },

        _getFilteredItems: function (data) {
            let result = [];
            for (let i = 0; i < data.length; i++) {
                let newItem = JSON.parse(JSON.stringify(data[i]));
                if (newItem.allSkus !== undefined) {
                    delete newItem.allSkus;
                }
                result.push(newItem);
            }
            return result;
        },

        getRemoveFromCart: function() {
            return this.getData('removeFromCart');
        },

        setCartData: function(data) {
            this._logData(data);
            for (let i = 0; i < data.length; i++) {
                data[i].quantity = parseFloat(data[i].quantity);
                data[i].price = this.formatPrice(data[i].price, true);
            }
            this.setData('cart', data);
            this.fire('begin_checkout', this._getFilteredItems(data));
        },

        setCheckoutStep: function(step) {
            this._logData(step);
            let stepData = {
                step: step.step,
                option: step.option,
                products: this._getFilteredItems(step.products),
                stepType: step.stepType
            };
            this.setData('checkoutStep', stepData);
            this.fire('checkout_step', stepData);
        },

        setCheckoutOption: function(option) {
            this._logData(option);
            this.setData('checkoutOption', option);
            this.fire('checkout_option', option);
        },

        getCartData: function() {
            return this.getData('cart');
        },

        setPurchaseData: function(data) {
            this._logData(data);
            if (data.affiliation == null) {
                data.affiliation = '';
            }
            if (data.coupon == null) {
                data.coupon = '';
            }
            data.revenue = this.formatPrice(data.revenue, true);
            data.tax = this.formatPrice(data.tax, true);
            data.shipping = this.formatPrice(data.shipping, true);
            let prods = data.products;
            let result = [];
            for (let i = 0; i < prods.length; i++) {
                let pr = prods[i];
                pr.quantity = parseFloat(pr.quantity);
                pr.price = this.formatPrice(pr.price, true);
                delete pr.allSkus;
                result.push(pr);
            }
            data.products = result;
            this.setData('purchase', data);
            this.fire('purchase', data);
        },

        getPurchaseData: function() {
            return this.getData('purchase');
        },

        findProductInList: function (id, list) {
            for (let i = 0; i < list.length; i++) {
                if (list[i]['id'] === id) {
                    return list[i];
                }
            }
            return '';
        },

        _getProductFromStorage: function(productId) {
            let tmpIds = [];
            if (Array.isArray(productId)) {
                tmpIds = productId;
            } else {
                tmpIds.push(productId);
            }
            let tmpList = JSON.parse(localStorage.getItem('sc-tracking-data'));
            if (tmpList != null) {
                let tmp = tmpList['product_list'];
                for (let i = 0; i < tmpIds.length; i++) {
                    let id = tmpIds[i];
                    let item = this.findProductInList(id, tmp);
                    if (item !== '') {
                        return item;
                    }
                }
            }
            return false;
        },

        getProductImpression: function(productId) {
            let item = this._getProductFromStorage(productId);
            if (item !== false && item !== '') {
                return item.list;
            }
            return this.getDefaultList();
        },

        setProductImpression: function(productId, list) {
            let tmpList = JSON.parse(localStorage.getItem('sc-tracking-data'));
            if (tmpList == null) {
                tmpList = [];
                tmpList.push({'id': productId, 'list': list});
                localStorage.setItem('sc-tracking-data', JSON.stringify({"product_list": tmpList}));
            } else {
                let tmp = tmpList['product_list'];
                let item = this.findProductInList(productId, tmp);
                if (item !== '') {
                    item['list'] = list;
                } else {
                    tmp.push({'id': productId, 'list': list});
                }
                localStorage.setItem('sc-tracking-data', JSON.stringify({'product_list': tmp}));
            }
        },

        clearProductImpressions: function () {
            localStorage.setItem('sc-tracking-data', null);
        },

        findProductByUrl: function (url) {
            for (let i = 0; i < this._internalData.productList.length; i++) {
                let item = this._internalData.productList[i];
                if (item.url === url) {
                    return item;
                }
            }
            return false;
        },

        setImpressionListData: function (listData, skipFireEvent) {
            this._logData(listData);
            if (listData === null || listData.length === 0) return;
            for (let i = 0; i < listData.length; i++) {
                this._internalData.productList.push(listData[i]);
                this.setProductImpression(listData[i].id, listData[i].list);
            }
            if (skipFireEvent === undefined || skipFireEvent !== true) {
                this.fire('view_list', listData);
            }
        },

        setProductData: function(data) {
            this._logData(data);
            data.list = this.getProductImpression(data.id);
            this._internalData.productData = data;
            this.fire('view_item', data);
        },

        getProductData: function() {
            return this.getData('productData');
        },

        getCategoryProducts: function() {
            return this.getData('productList');
        },

        setScrollImpression: function(data, fixPosition) {
            this._logData(data);
            if (fixPosition === undefined || fixPosition === true) {
                var existingList = this._internalData.productList;
                var lastPos = existingList.length;
                for (var i = 0; i < data.length; i++) {
                    data[i].position = lastPos + i + 1;
                    this._internalData.productList.push(data[i]);
                    this.setProductImpression(data[i].id, data[i].list);
                }
            }
            this.fire('listing_scroll', data);
        },

        subscribe: function(eventName, callback) {
            this._internalData.subscribers[eventName].push(callback);
        },

        fire: function(eventName, data) {
            if (this._internalData._fireEvents === false) {
                this._internalData._eventsQueue.push({
                    'eventName': eventName,
                    'data': data
                });
                return;
            }
            for (let i = 0; i < this._internalData.subscribers[eventName].length; i++) {
                this._internalData.subscribers[eventName][i](data);
            }
        },

        sendQuoteImpression: function(productId, list) {
            $.post({
                url: url.build('sctracking/index/saveimpression'),
                data: {
                    id: productId,
                    list: list
                }
            })
        }
    });

    return window.scTrackingContainer;
});
