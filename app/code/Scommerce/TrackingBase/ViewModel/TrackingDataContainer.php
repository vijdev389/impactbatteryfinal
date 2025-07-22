<?php
/**
 * Tracking Base view model for base data
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class TrackingDataContainer
 * @package Scommerce\TrackingBase\ViewModel
 */
class TrackingDataContainer extends DataObject implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @var CartRepositoryInterface
     */
    protected $_cartRepository;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var null | array
     */
    private $_cartData = null;

    /**
     * @var null | array
     */
    private $_cartOutData = null;

    /**
     * TrackingDataContainer constructor.
     * @param Data $helper
     * @param Session $checkoutSession
     * @param SessionManagerInterface $coreSession
     * @param CartRepositoryInterface $cartRepository
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Session $checkoutSession,
        SessionManagerInterface $coreSession,
        CartRepositoryInterface $cartRepository,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_coreSession = $coreSession;
        $this->_cartRepository = $cartRepository;
        $this->_customerSession = $customerSession;
        parent::__construct($data);
    }

    /**
     * @param $block Template
     * @return string
     */
    public function getPageType(Template $block): string
    {
        if ($block->getData('page_type')) {
            return $block->getData('page_type');
        }
        return 'other';
    }

    public function getNonce()
    {
        return $this->_helper->getNonce();
    }

    /**
     * @return mixed
     */
    public function getDefaultList()
    {
        return $this->_helper->getDefaultList();
    }

    /**
     * @return mixed
     */
    public function getSendFullList()
    {
        return $this->_helper->getSendFullList();
    }

    /**
     * @return mixed
     */
    public function getSendDefaultList()
    {
        return $this->_helper->getSendDefaultList();
    }

    public function isConsentModeEnabled()
    {
        return $this->_helper->isConsentModeEnabled();
    }

    public function getConsentCookiesConfiguration()
    {
        return $this->_helper->getConsentCookiesConfiguration();
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrency()
    {
        return $this->_helper->getCurrency();
    }

    /**
     * @return array|mixed|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductToBasket()
    {
        if ($this->_cartData === null) {
            if ($this->_coreSession->getProductToBasket()) {
                $data = json_decode($this->_coreSession->getProductToBasket(), true);
                if (isset($data['_realProductId'])) {
                    $quoteItem = $this->getQuoteItem($data['_realProductId']);
                    if ($quoteItem) {
                        if (isset($data['_isWishlist']) && $data['_isWishlist'] == 1) {
                            $data['list'] = 'Wishlist';
                            $this->_helper->setQuoteItemTrackingData($quoteItem, 'list', 'Wishlist', 'parent');
                            $this->_cartRepository->save($this->_checkoutSession->getQuote());
                        }
                    }
                }
                $this->_cartData = $data;
                $this->_coreSession->unsProductToBasket();
            }
        }
        return $this->_cartData;
    }

    public function getProductToWishlist()
    {
        $data = $this->_coreSession->getProductToWishlist();
        if ($data) {
            $this->_coreSession->unsProductToWishlist();
            return json_decode($data, true);
        }
        return null;
    }

    /**
     * @return array|mixed|null
     */
    public function getProductOutBasket()
    {
        if ($this->_cartOutData === null) {
            if ($this->_coreSession->getProductOutBasket()) {
                $this->_cartOutData = $this->_coreSession->getProductOutBasket();
                $this->_coreSession->unsProductOutBasket();
            }
        }
        return $this->_cartOutData;
    }

    /**
     * @param $productId
     * @return mixed|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getQuoteItem($productId)
    {
        $quote = $this->_checkoutSession->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductId() == $productId) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helper->isEnabled();
    }

    /**
     * @return int
     */
    public function isGuest()
    {
        return $this->_customerSession->isLoggedIn() ? 0 : 1;
    }
}
