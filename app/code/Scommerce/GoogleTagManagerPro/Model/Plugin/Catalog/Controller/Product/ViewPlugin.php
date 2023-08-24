<?php

/**
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GoogleTagManagerPro\Model\Plugin\Catalog\Controller\Product;

use Scommerce\GoogleTagManagerPro\Helper\Data;
use Scommerce\GoogleTagManagerPro\Model\Session;
use Magento\Framework\Registry;
use Magento\Catalog\Controller\Product\View;

/**
 * Class ViewPlugin
 * @package Scommerce\GoogleTagManagerPro\Model\Plugin\Catalog\Controller\Product
 */
class ViewPlugin
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Session
     */
    protected $_gtmSession;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * ViewPlugin constructor.
     * @param Registry $coreRegistry
     * @param Session $gtmSession
     * @param Data $helper
     */
    public function __construct(
        Registry $coreRegistry,
        Session $gtmSession,
        Data $helper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_gtmSession = $gtmSession;
        $this->_helper = $helper;
    }

    /**
     * @param View $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(View $subject, $result)
    {
        $category = $this->_coreRegistry->registry('current_category');
        $product = $this->_coreRegistry->registry('current_product');

        if ($category && $product) {
            $this->_gtmSession->setCategoryForProduct($product->getId(), $this->_helper->getCategoryPath($category));
        }
        $this->_gtmSession->clearTrackingData();

        return $result;
    }
}
