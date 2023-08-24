<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GoogleTagManagerPro\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Scommerce\GoogleTagManagerPro\Helper\Data;
use Scommerce\GoogleTagManagerPro\Model\Session as GtmSession;
use Magento\Framework\Stdlib\CookieManagerInterface;

class ProductView implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var GtmSession
     */
    protected $_gtmSession;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var SessionManagerInterface
     */
    protected $_session;

    /**
     * @var CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * ProductView constructor.
     * @param Data $helper
     * @param GtmSession $gtmSession
     * @param CookieManagerInterface $cookieManager
     * @param SessionManagerInterface $session
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Data $helper,
        GtmSession $gtmSession,
        CookieManagerInterface $cookieManager,
        SessionManagerInterface $session,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->_helper = $helper;
        $this->_gtmSession = $gtmSession;
        $this->_cookieManager = $cookieManager;
        $this->_session = $session;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isEnhancedEcommerceEnabled() && $this->_helper->isEnabled()){
            $product = $observer->getEvent()->getProduct();
            $category = $this->_cookieManager->getCookie('sc_category');
            if ($category) {
                $this->_gtmSession->setCategoryForProduct($product->getId(), $category);
                $metadata = $this->_cookieMetadataFactory->createCookieMetadata()
                    ->setPath($this->_session->getCookiePath());
                $this->_cookieManager->deleteCookie('sc_category', $metadata);
            }
        }
    }
}
