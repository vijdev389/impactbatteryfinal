<?php
/**
 * Copyright © 2016 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Scommerce\GoogleTagManagerPro\Block;

/**
 * Google Tag Manager Pro Script Block
 */
class Script extends \Magento\Framework\View\Element\Template
{

    /**
     * Google Remarketing Allowed Page Types
     * @see https://support.google.com/adwords/answer/3103357?hl=en
     */
    private $_allowedPageTypes 	= ['home','searchresults','category','product','cart','checkout','purchase','other'];

    /**
     * Default product attribute to use for
     */
    private $_productAttribute 	= 'sku';

    /**
     * Default cart and sales attribute to use
     */
    private $_saleAttribute 	= 'product_id';

    /**
     * Default pagetype
     */
    private $_pagetype			= 'other';

    /**
     * @var \Magento\Catalog\Model\Category
     */

    protected $_category;

    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_salesFactory;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Scommerce\GoogleTagManagerPro\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productLoader;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Scommerce\GoogleTagManagerPro\Helper\Data $helper
     * @param \Magento\Sales\Model\Order $salesOrderFactory
     * @param \Magento\Catalog\Model\ProductFactory $productLoader
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Pricing\Helper\Data
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\GoogleTagManagerPro\Helper\Data $helper,
        \Magento\Sales\Model\Order $salesOrderFactory,
        \Magento\Catalog\Model\ProductFactory $productLoader,
        \Magento\Catalog\Model\Category $category,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        $this->_salesFactory = $salesOrderFactory;
        $this->_productLoader = $productLoader;
        $this->_category = $category;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->_jsonHelper = $jsonHelper;
        $this->_pricingHelper = $pricingHelper;
        $this->_productAttribute 	= $this->getHelper()->getProductAttributeKey();
        parent::__construct($context, $data);
    }

    /**
     * Return catalog product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_registry->registry('product');
    }

    /**
     * Return catalog category object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * Retrieve current order
     *
     * @return \Magento\Sales\Model\Order\OrderFactory
     */
    public function getOrder()
    {
        $orderId = $this->_checkoutSession->getLastOrderId();
        return $this->_salesFactory->load($orderId);
    }

    /**
     * Set current pagetype
     * @param $pagetype
     */
    public function setPageType($pagetype)
    {
        if (in_array(strtolower($pagetype), $this->_allowedPageTypes)) {
            $this->_pagetype = strtolower($pagetype);
        }
    }

    /**
     * get current pagetype
     * @return string
     */
    public function getPageType()
    {
        return $this->_pagetype;
    }

    /**
     * return product attribute sent as part of Google Base Feed
     * @param $attributeName
     */
    public function setProductAttributeName($attributeName)
    {
        $this->_productAttribute = strtolower($attributeName);
    }

    /**
     * Return dynamic remarketing data based on the page type
     * @return array
     */
    public function getJsConfigParams()
    {
        /**
         * Default parameters
         */
        if ($this->_helper->isOtherSiteEnabled()) {
            $_params = [
                'dynx_pagetype' => $this->_pagetype,
                'dynx_itemid' => '',
                'dynx_totalvalue' => 0
            ];
        } else {
            $_params = [
                'ecomm_pagetype' => $this->_pagetype,
                'ecomm_prodid' => '',
                'ecomm_totalvalue' => 0
            ];
        }

        switch ($this->_pagetype) {
            default:
                break;

            case 'product':
                $_params = array_merge($_params, $this->collectCurrentProductData());
                break;

            case 'category':
                $_params = array_merge($_params, $this->collectCurrentCategoryData());
                break;

            case 'cart':
            case 'checkout':
                $_params = array_merge($_params, $this->collectCurrentCartData());
                break;

            case 'purchase':
                $_params = array_merge($_params, $this->collectCurrentOrderData());
                break;
        }

        return $this->sanitizeParams($_params);
    }

    /**
     * Collect the data from current product
     * @return array
     */
    private function collectCurrentProductData()
    {
        $_product = $this->getProduct();
        $_params = [];
        if ($_product && $_product instanceof \Magento\Catalog\Model\Product) {
            if ($this->_helper->isOtherSiteEnabled()) {
                $_params['dynx_pagetype'] 	 = 'offerdetail';
                $_params['dynx_itemid'] 	 = $this->getProdId($_product, 'catalog');
                $_params['dynx_totalvalue']  = $this->formatPrice($_product->getFinalPrice());
            } else {
                $_params['ecomm_prodid'] 	 = $this->getProdId($_product, 'catalog');
                $_params['ecomm_totalvalue'] = $this->formatPrice($_product->getFinalPrice());
                $_params['ecomm_pvalue'] 	 = $this->formatPrice($_product->getFinalPrice());

                if ($this->getCategory()) {
                    $_params['ecomm_category'] = $this->getCategory()->getName();
                }
            }
        }
        return $_params;
    }

    /**
     * Collect the data from current category
     * @return array
     */
    private function collectCurrentCategoryData()
    {
        $_category = $this->getCategory();
        $_params = [];
        if ($_category && $_category instanceof \Magento\Catalog\Model\Category) {
            $products = [];
            $prices = [];
            $total = 0;
            $_productCollection = $this->_layout->getBlockSingleton('Magento\Catalog\Block\Product\ListProduct')->getLoadedProductCollection();
            if ($_category->getDisplayMode()!=$this->_helper->getCMDisplayMode()) {
                foreach ($_productCollection as $_product) {
                    $products[] = $this->getProdId($_product, 'catalog');
                    $total = $total + (float)$_product->getFinalPrice();
                }
            }

            if ($this->_helper->isOtherSiteEnabled()) {
                $_params['dynx_pagetype'] 	 = 'other';
                $_params['dynx_itemid'] 	 = $products;
                $_params['dynx_totalvalue']  = $total;
            } else {
                $_params['ecomm_prodid'] = $products;
                $_params['ecomm_totalvalue'] = $total;
            }
        }
        return $_params;
    }

    /**
     * Collect data from the shopping cart page
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function collectCurrentCartData()
    {
        $_quotation = $this->_checkoutSession->getQuote();
        if ($_quotation && $_quotation instanceof \Magento\Quote\Model\Quote) {
            $qtys		= [];
            $products 	= [];

            foreach ($_quotation->getAllVisibleItems() as $_product) {
                $qtys[] 	= number_format($_product->getQty(), 0);
                $products[] = $this->getProdId($_product, 'sales');
            }
            $_params = [];
            if ($this->_helper->isOtherSiteEnabled()) {
                $_params['dynx_pagetype'] 	 = 'conversionintent';
                $_params['dynx_itemid'] 	 = $products;
                $_params['dynx_totalvalue']  = $this->formatPrice($_quotation->getGrandTotal());
                $_params['dynx_quantity'] 	 = $qtys;
            } else {
                $_params['ecomm_prodid']	 = $products;
                $_params['ecomm_totalvalue'] = $this->formatPrice($_quotation->getGrandTotal());
                $_params['ecomm_quantity'] 	 = $qtys;
            }
            return $_params;
        }
    }

    /**
     * Collect data from the current order
     * @return array|bool
     */
    private function collectCurrentOrderData()
    {
        $_order = $this->getOrder();
        if ($_order && $_order instanceof \Magento\Sales\Model\Order) {
            $total = $_order->getGrandTotal();
            $qtys = [];
            $products = [];
            $prices = [];

            foreach ($_order->getAllVisibleItems() as $_product) {
                $products[] = $this->getProdId($_product, 'sales');
                $qtys[] = number_format($_product->getQtyOrdered(), 0);
                $prices[] = number_format($_product->getPrice(), 2);
            }

            $_params = [];
            if ($this->_helper->isOtherSiteEnabled()) {
                $_params['dynx_pagetype'] 	 = 'conversion';
                $_params['dynx_itemid'] 	 = $products;
                $_params['dynx_totalvalue']  = $this->formatPrice($total);
                $_params['dynx_quantity'] 	 = $qtys;
            } else {
                $_params['ecomm_prodid'] 	 = $products;
                $_params['ecomm_totalvalue'] = $this->formatPrice($total);
                $_params['ecomm_quantity']   = $qtys;
                $_params['ecomm_pvalue']     =  $prices;
            }

            $_params['hasaccount'] 		 = ($_order->getCustomerIsGuest() == 1) ? 'N' : 'Y';

            return $_params;
        }

        return false;
    }

    /**
     * Formats a price in store currency settings
     * @param $price
     * @return mixed
     */
    private function formatPrice($price)
    {
        return $this->_pricingHelper->currency($price, false, false);
    }

    /**
     * Return helper object
     *
     * @return \Scommerce\GoogleTagManagerPro\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Gets prod id attribute string from product object
     * @param $_product
     * @param $type
     * @return mixed
     */
    private function getProdId($_product, $type)
    {
        if ($type =="sales") {
            $product = $this->_productLoader->create()->load($_product->getData($this->_saleAttribute));
            return $product->getData($this->_productAttribute);
        } else {
            return $_product->getData($this->_productAttribute);
        }
    }

    /**
     * Render block html if google dynamic remarketing is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_helper->getDynamicRemarketingEnabled() ? parent::_toHtml() : '';
    }

    /**
     * @param $params
     * @return string|string[]|null
     */
    private function sanitizeParams($params)
    {
        $param = preg_replace(
            '/"([^"]+)"s*:s*/',
            '$1: $2',
            $this->_jsonHelper->jsonEncode($params)
        );

        $param = str_replace('",', '",' . chr(13), $param);
        $param = str_replace('],', '],' . chr(13), $param);
        $param = str_replace('},', '},' . chr(13), $param);
        $param = str_replace('{', '{' . chr(13), $param);
        $param = str_replace('}', chr(13) . '}', $param);
        return $param;
    }
}
