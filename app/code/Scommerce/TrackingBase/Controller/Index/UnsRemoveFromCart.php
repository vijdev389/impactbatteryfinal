<?php
/**
 * Scommerce TrackingBase unset remove from cart data controller
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Context;

/**
 * Class UnsRemoveFromCart
 * @package Scommerce\TrackingBase\Controller\Index
 */
class UnsRemoveFromCart extends Action
{
    /**
     * @var SessionManagerInterface
     */
	protected $_coreSession;

    /**
     * @var JsonFactory
     */
    protected $_jsonFactory;

    /**
     * UnsRemoveFromCart constructor.
     * @param Context $context
     * @param SessionManagerInterface $coreSession
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
		Context $context,
        SessionManagerInterface $coreSession,
        JsonFactory $jsonFactory
    ) {
        $this->_coreSession = $coreSession;
        $this->_jsonFactory = $jsonFactory;
		parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
		if ($this->_coreSession->getProductOutBasket()) {
			$this->_coreSession->unsProductOutBasket();
		}
        $jsonResult = $this->_jsonFactory->create();
        return $jsonResult->setData([]);
    }
}
