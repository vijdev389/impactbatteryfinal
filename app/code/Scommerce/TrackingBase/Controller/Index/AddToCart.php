<?php
/**
 * Scommerce TrackingBase add to cart ajax controller
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class AddToCart
 * @package Scommerce\TrackingBase\Controller\Index
 */
class AddToCart extends Action
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
     * AddToCart constructor.
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
        $result = $this->_jsonFactory->create();
        $data = [];
        $productToBasket = $this->_coreSession->getProductToBasket();
        if ($productToBasket) {
            $data = json_decode($productToBasket, true);
        }
        return $result->setData($data);
    }
}
