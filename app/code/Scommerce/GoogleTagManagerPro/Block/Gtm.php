<?php
/**
 * Google Tag Manager Pro block
 *
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GoogleTagManagerPro\Block;

class Gtm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Scommerce\GoogleTagManagerPro\Helper\Data
     */
    protected $_gtmpData;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_salesFactory;
	
	/**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
	protected $_coreSession;
	
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Scommerce\GoogleTagManagerPro\Helper\Data $gtmpData
	 * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order $salesOrderFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\GoogleTagManagerPro\Helper\Data $gtmpData,
	    \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $salesOrderFactory,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->_gtmpData = $gtmpData;
        $this->_checkoutSession = $checkoutSession;
        $this->_salesFactory = $salesOrderFactory;
        $this->_request = $request;
		$this->_coreSession = $context->getSession();
        parent::__construct($context, $data);
    }

    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName()
    {
        if (!$this->hasData('page_name')) {
            $this->setPageName($this->escapeJsQuote($_SERVER['REQUEST_URI']));
        }
        return $this->getData('page_name');
    }

    /**
     * Retrieve domain url without www or subdomain
     *
     * @return string
     */
    public function getMainDomain()
    {
        if (!$this->hasData('main_domain')) {
            $host = $this->_request->getHttpHost();
            if (substr_count($host,'.')>1 && (!$this->getHelper()->isDomainAuto())){
                $this->setMainDomain(substr($host,strpos($host,'.')+1));
            }
            else{
                $this->setMainDomain('auto');
            }
        }
        return $this->getData('main_domain');
    }

    /**
     * Retrieve domain url without www or subdomain
     *
     * @return string
     */
    public function getDomain()
    {
        if (!$this->hasData('domain')) {
            $host = $this->_request->getHttpHost();
            if (substr_count($host,'.')>1){
                $this->setDomain(substr($host,strpos($host,'.')+1));
            }
        }
        return $this->getData('domain');
    }

    /**
     * Render block html if Google Tag Manager is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getHelper()->isEnabled() ? parent::_toHtml() : '';
    }

    /**
     * @return \Scommerce\GoogleTagManagerPro\Helper\Data
     */
    public function getHelper()
    {
        return $this->_gtmpData;
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
     * Return if it is order confirmation page or not
     *
     * @return boolean
     */
    public function isEcommerce()
    {
        if ((strpos($this->getPageName(), 'success')!==false) && (strpos($this->getPageName(), 'checkout')!==false)){
            return true;
        }
        return false;
    }
	
	/**
     * Return add to basket product data
     *
     * @return json
     */
    public function getAddToBasketData()
    {
        return $this->_coreSession->getProductToBasket();
    }
	
	/**
     * Remove add to basket product data
     *
     */
    public function unsAddToBasketData()
    {
        $this->_coreSession->unsProductToBasket();
    }
	
	/**
     * Return remove from basket product data
     *
     * @return json
     */
    public function getRemoveFromBasketData()
    {
        return $this->_coreSession->getProductOutBasket();
    }
	
	/**
     * Remove remove from basket product data
     *
     */
    public function unsRemoveFromBasketData()
    {
        return $this->_coreSession->unsProductOutBasket();
    }
}