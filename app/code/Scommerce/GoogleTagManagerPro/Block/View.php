<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Scommerce\GoogleTagManagerPro\Block;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Scommerce\GoogleTagManagerPro\Helper\Data;
use Scommerce\GoogleTagManagerPro\Model\Session;

/**
 * Catalog Product View Page Block
 */
class View extends \Magento\Framework\View\Element\Template
{

    /**
     * @var Registry
     */

    protected $_registry;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var Session
     */
    protected $_gtmSession;

    /**
     * @param Context $context
     * @param Data $helper
     * @param Product $product
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Data $helper,
        Product $product,
        Registry $registry,
        Session $gtmSession,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_helper = $helper;
        $this->_product = $product;
        $this->_gtmSession = $gtmSession;
        parent::__construct($context, $data);
    }

    /**
     * Return catalog product object
     *
     * @return Product
     */

    public function getProduct()
    {
        return $this->_registry->registry('product');
    }

    /**
     * Return catalog product collection
     *
     * @param $_productIds
     * @return Collection
     */
    public function getProducts($_productIds)
    {
        return $this->getProduct()
            ->getCollection()
            ->addAttributeToSelect(array('name','sku','price'))
            ->addAttributeToFilter('entity_id',array('in' => $_productIds))
            ->addUrlRewrite();
    }

    /**
     * @param $product
     * @param $categoryName
     * @return array|string
     */
    public function getList($product, $categoryName)
    {
        $list = $this->_gtmSession->getImpressionForProduct($product->getSku());
        if (!$list) {
            $list = '';
        } else {
            $list = $this->escapeJsQuote($list);
        }
        return $list;
    }

    /**
     * Return catalog current category object
     *
     * @return Category
     */

    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * Return helper object
     *
     * @return Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Render block html if google universal analytics conversion is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_helper->isEnabled() ? parent::_toHtml() : '';
    }
}
