<?php
/**
 * Scommerce TrackingBase save impression controller
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class SaveImpression
 * @package Scommerce\TrackingBase\Controller\Index
 */
class SaveImpression extends Action
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
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var CartRepositoryInterface
     */
    protected $_cartRepository;

    /**
     * UnsAddToCart constructor.
     * @param Context $context
     * @param SessionManagerInterface $coreSession
     * @param JsonFactory $jsonFactory
     * @param Session $checkoutSession
     * @param Data $helper
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
		Context $context,
        SessionManagerInterface $coreSession,
        JsonFactory $jsonFactory,
        Session $checkoutSession,
        Data $helper,
        CartRepositoryInterface $cartRepository
    ) {
        $this->_coreSession = $coreSession;
        $this->_jsonFactory = $jsonFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_cartRepository = $cartRepository;
		parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
		$productId = $this->_request->getParam('id');
		$list = $this->_request->getParam('list');
		if ($productId && $list) {
		    $quote = $this->_checkoutSession->getQuote();
		    foreach ($quote->getAllItems() as $item) {
		        if ($item->getProductId() == $productId) {
		            $this->_helper->setQuoteItemTrackingData($item, 'list', $list, 'parent');
		            break;
                }
            }
		    $this->_cartRepository->save($quote);
        }
        return $this->_jsonFactory->create();
    }
}
