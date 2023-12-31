define([
    'jquery'
], function ($) {
    let Remarketing = function(tracking, pageType, remarketingType, sendCategoryPath) {
        let result = {};

        this.formatPrice = function(priceValue) {
            let val = priceValue;
            if (typeof val === 'string')
            {
                val = val.replace(/,/g, '');
            }
            return parseFloat(parseFloat(val).toFixed(2));
        };

        this.getProductData = function(data) {
            let productData = tracking.getProductData();
            let price = this.formatPrice(productData.price);
            if (remarketingType === 1) {
                data.dynx_pagetype = 'offerdetail';
                data.dynx_itemid = productData.id;
                data.dynx_totalvalue = price;
            } else {
                data.ecomm_prodid = productData.id;
                data.ecomm_totalvalue = price;
                data.ecomm_pvalue = price;
                data.ecomm_category = sendCategoryPath === 1 ? tracking.getData('category_full') : tracking.getData('category_plain');
            }
            return data;
        };

        this.getCategoryData = function(data) {
            let total = 0;
            let tmp = tracking.getCategoryProducts();
            let products = [];
            for (let i = 0; i < tmp.length; i++) {
                products.push(tmp[i].id);
                let val = tmp[i].price;
                if (typeof val === 'string')
                {
                    val = val.replace(/,/g, '');
                }
                total += parseFloat(val);
            }
            total = this.formatPrice(total);
            if (remarketingType === 1) {
                data.dynx_pagetype = 'other';
                data.dynx_itemid = products;
                data.dynx_totalvalue = total;
            } else {
                data.ecomm_prodid = products;
                data.ecomm_totalvalue = total;
            }
            return data;
        };

        this.getCartData = function(data) {
            let total = tracking.getData('total');
            let tmp = tracking.getCartData();
            let qtys = [];
            let products = [];
            for (let i = 0; i < tmp.length; i++) {
                products.push(tmp[i].id);
                qtys.push(tmp[i].quantity);
            }
            total = this.formatPrice(total);
            if (remarketingType === 1) {
                data.dynx_pagetype = 'conversionintent';
                data.dynx_itemid = products;
                data.dynx_totalvalue = total;
                data.dynx_quantity = parseFloat(qtys);
            } else {
                data.ecomm_prodid = products;
                data.ecomm_totalvalue = total;
                data.ecomm_quantity = parseFloat(qtys);
            }
            return data;
        };

        this.getPurchaseData = function(data) {
            let purchaseData = tracking.getPurchaseData();
            let total = purchaseData.revenue;
            let tmp = purchaseData.products;
            let qtys = [];
            let products = [];
            let prices = [];
            for (let i = 0; i < tmp.length; i++) {
                products.push(tmp[i].id);
                qtys.push(parseFloat(tmp[i].quantity));
                let val = tmp[i].price;
                if (typeof val === 'string')
                {
                    val = val.replace(/,/g, '');
                }
                prices.push(parseFloat(val));
            }
            total = this.formatPrice(total);
            if (remarketingType === 1) {
                data.dynx_pagetype = 'conversion';
                data.dynx_itemid = products;
                data.dynx_totalvalue = total;
                data.dynx_quantity = parseFloat(qtys);
            } else {
                data.ecomm_prodid = products;
                data.ecomm_totalvalue = total;
                data.ecomm_quantity = parseFloat(qtys);
                data.ecomm_pvalue = prices;
            }
            data.hasaccount = tracking.getData('isGuest') === 1 ? 'N' : 'Y';
            return data;
        };

        if (remarketingType === 1) {
            result.dynx_pagetype = pageType;
            result.dynx_itemid = '';
            result.dynx_totalvalue = 0;
        } else {
            result.ecomm_pagetype = pageType;
            result.ecomm_prodid = '';
            result.ecomm_totalvalue = 0;
        }
        switch (pageType) {
            case 'product':
                result = this.getProductData(result);
                break;
            case 'category':
                result = this.getCategoryData(result);
                break;
            case 'cart':
            case 'checkout':
                result = this.getCartData(result);
                break;
            case 'purchase':
                result = this.getPurchaseData(result);
                break;
        }

        return result;
    };
    if ($.breezemap) {
        $.breezemap['remarketing'] = Remarketing;
    }
    return Remarketing;
});
