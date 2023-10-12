<?php
/**
 * Scommerce TrackingBase Remove from cart controller
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Action;

/**
 * Class RemoveFromCart
 * @package Scommerce\TrackingBase\Controller\Index
 */
class RemoveFromCart extends Action {

    /**
     * @var SessionManagerInterface
     */
	protected $_coreSession;

    /**
     * @var JsonFactory
     */
    protected $_jsonFactory;

    /**
     * RemoveFromCart constructor.
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
     * Returns remove from basket product data
     *
     * @return string
     */
    public function execute()
    {
        $result = $this->_jsonFactory->create();
        $data = [];
        $productOutBasket = $this->_coreSession->getProductOutBasket();
        if ($productOutBasket) {
            $data = json_decode($productOutBasket, true);
        }
        return $result->setData($data);
    }
}
