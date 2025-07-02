<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GoogleTagManagerPro\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Scommerce\GoogleTagManagerPro\Model\Session;


class CheckoutRemoveItemAfter implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Scommerce\GoogleTagManagerPro\Helper\Data
     */
    protected $_helper;

	/**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
	protected $_coreSession;

    /**
     * @var Session
     */
	protected $_gtmSession;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magento\Framework\Session\SessionManagerInterface $coresession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Scommerce\GoogleTagManagerPro\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Session\SessionManagerInterface $coresession,
        \Magento\Framework\App\Request\Http $request,
        \Scommerce\GoogleTagManagerPro\Helper\Data $helper,
        Session $gtmSession
    ) {
        $this->_objectManager = $objectmanager;
        $this->_coreSession = $coresession;
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_gtmSession = $gtmSession;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isEnhancedEcommerceEnabled() && $this->_helper->isEnabled()){

            $quoteItem = $observer->getQuoteItem();
            $product = $quoteItem->getProduct();
            $sku = $this->_helper->getParentSKU($quoteItem);
            $list = $this->getImpressionList($quoteItem);

            $productOutBasket = array(
                    'id' => $sku,
                    'name' => $product->getName(),
                    'category' => $this->_helper->getProductCategoryName($product),
                    'brand' => $this->_helper->getBrand($product),
                    'price' => number_format($product->getFinalPrice(),2),
                    'qty'=> 0,
					'list' => $list,
					'currency' => $this->_helper->getCurrencyCode()
            );

            $this->_gtmSession->unsetProductData($quoteItem->getProduct()->getId());
			$this->_coreSession->setProductOutBasket(json_encode($productOutBasket));
        }
    }

    protected function getImpressionList($quoteItem)
    {
        $trackingData = $quoteItem->getScTrackingData();
        if (!$trackingData) {
            return '';
        }
        $trackingData = json_decode($trackingData, true);
        return isset($trackingData['list']) ? $trackingData['list'] : '';
    }
}
