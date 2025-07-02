<?php
/**
 * Success page view model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\ViewModel\Checkout;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Scommerce\TrackingBase\Model\GetProductId;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Scommerce\TrackingBase\Helper\Data;
use Magento\Framework\View\Element\Template;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetCategoryPath;
use Scommerce\TrackingBase\Model\GetParentProduct;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductPrice;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteRepository;
use Scommerce\TrackingBase\Model\GetProductVariant;

/**
 * Class Cart
 * @package Scommerce\TrackingBase\ViewModel
 */
class Success extends DataObject implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Template
     */
    protected $_block;

    /**
     * @var GetProductCategory
     */
    protected $_getCategory;

    /**
     * @var GetBrand
     */
    protected $_getBrand;

    /**
     * @var GetProductPrice
     */
    protected $_getProductPrice;

    /**
     * @var GetCategoryPath
     */
    protected $_getCategoryPath;

    /**
     * @var GetProductCategory
     */
    protected $_getProductCategory;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var GetParentProduct
     */
    protected $_getParentProduct;

    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @var GetProductId
     */
    protected $_getProductId;

    /**
     * @var GetProductVariant
     */
    protected $_getProductVariant;

    /**
     * @param Data $helper
     * @param GetProductCategory $getCategory
     * @param GetBrand $getBrand
     * @param GetProductPrice $getProductPrice
     * @param GetCategoryPath $getCategoryPath
     * @param GetProductCategory $getProductCategory
     * @param CheckoutSession $checkoutSession
     * @param GetParentProduct $getParentProduct
     * @param ItemFactory $quoteItemFactory
     * @param Item $itemResourceModel
     * @param GetProductId $getProductId
     * @param GetProductVariant $getProductVariant
     * @param CountryFactory $countryFactory
     * @param QuoteRepository $quoteRepository
     * @param array $data
     */
    public function __construct(
        Data $helper,
        GetProductCategory $getCategory,
        GetBrand $getBrand,
        GetProductPrice $getProductPrice,
        GetCategoryPath $getCategoryPath,
        GetProductCategory $getProductCategory,
        CheckoutSession $checkoutSession,
        GetParentProduct $getParentProduct,
        QuoteRepository $quoteRepository,
        GetProductId $getProductId,
        GetProductVariant $getProductVariant,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_getCategory = $getCategory;
        $this->_getBrand = $getBrand;
        $this->_getProductPrice = $getProductPrice;
        $this->_getCategoryPath = $getCategoryPath;
        $this->_getProductCategory = $getProductCategory;
        $this->_checkoutSession = $checkoutSession;
        $this->_getParentProduct = $getParentProduct;
        $this->_quoteRepository = $quoteRepository;
        $this->_getProductId = $getProductId;
        $this->_getProductVariant = $getProductVariant;
        parent::__construct($data);
    }

    /**
     * @param $block
     */
    public function setBlock($block)
    {
        $this->_block = $block;
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
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPurchaseData()
    {
        $_products = [];
        $order = $this->getOrder();
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            list($id, $allSkus, $_realId) = $this->_getProductId->getIdsForItem($item);
            $name = $item->getProduct()->getName();
            $brand = $this->_getBrand->execute($item->getProduct(), true);
            $category = $this->_getProductCategory->execute($item->getProduct());
            $quoteItem = $this->getQuoteItem($item->getQuoteItemId(), $order->getQuoteId());
            $list = $this->_helper->getImpressionListFromQuoteItem($quoteItem);
            $_products[] = [
                'name' => $this->_helper->escapeJsQuote(trim($name)),
                'id' => $this->_helper->escapeJsQuote($id),
                'price' => $this->_getProductPrice->execute($item),
                'brand' => $this->_helper->escapeJsQuote($brand),
                'category' => $this->_helper->escapeJsQuote($category),
                'quantity' => $item->getQtyOrdered(),
                'allSkus' => $allSkus,
                'list' => $list,
                'variant' => $this->_getProductVariant->execute($item->getProduct(), $item)
            ];
        }
        $result = $this->getBasicData();
        $result['products'] = $_products;
        return $result;
    }

    /**
     * @return array
     */
    public function getBasicData()
    {
        $order = $this->getOrder();
        if ($this->_helper->sendBaseData()) {
            $orderCurrency = $order->getBaseCurrencyCode();
            $orderGrandTotal = $this->_helper->isOrderTotalIncludedVAT() ? $order->getBaseGrandTotal() : $order->getBaseGrandTotal() - $order->getBaseTaxAmount();
            $orderShippingTotal = $order->getBaseShippingAmount();
            $orderTax = $order->getBaseTaxAmount();
        } else {
            $orderCurrency = $order->getOrderCurrencyCode();
            $orderGrandTotal = $this->_helper->isOrderTotalIncludedVAT() ? $order->getGrandTotal() : $order->getGrandTotal() - $order->getTaxAmount();
            $orderShippingTotal = $order->getShippingAmount();
            $orderTax = $order->getTaxAmount();
        }
        return [
            'id' => $order->getIncrementId(),
            'affiliation' => $order->getAffiliation(),
            'revenue' => $orderGrandTotal,
            'tax' => $orderTax,
            'shipping' => $orderShippingTotal,
            'coupon' => $order->getCouponCode(),
            'currency' => $orderCurrency
        ];
    }

    /**
     * @return array
     */
    public function getConversionData()
    {
        $order = $this->getOrder();
        $email = $order->getCustomerEmail();
        $emailhash = hash('sha256', $email);
        $firstname = $order->getBillingAddress()->getFirstname();
        $firstnamehash =  hash('sha256', $firstname);
        $lastname = $order->getBillingAddress()->getLastname();
        $lastnamehash =  hash('sha256', $lastname);
        
        $resultArray = [
            'email' => $email,
            'emailhash' => $emailhash,
            'address' => [
                'first_name' => $firstname,
                'first_namehash' => $firstnamehash,
                'last_name' => $lastname,
                'last_namehash' => $lastnamehash,
                'street' => implode(', ', $order->getBillingAddress()->getStreet()),
                'city' => $order->getBillingAddress()->getCity(),
                'postal_code' => $order->getBillingAddress()->getPostcode(),
                'country' => $order->getBillingAddress()->getCountryId(),
            ]
        ];
        if ($region = $order->getBillingAddress()->getRegion()) {
            $resultArray['address']['region'] = $region;
        }
        if ($phone = $order->getBillingAddress()->getTelephone()) {
            $phonehash =  hash('sha256', $phone);
            $resultArray['phone_number'] = $phone;
            $resultArray['phone_numberhash'] = $phonehash;
        }
        return $resultArray;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCartItems()
    {
        $quote = $this->getQuote();
        return $quote->getAllItems();
    }

    /**
     * @return CartInterface|Quote
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * @param $quoteItemId
     * @param $quoteId
     * @return false|Quote\Item
     * @throws NoSuchEntityException
     */
    public function getQuoteItem($quoteItemId, $quoteId)
    {
        $quote = $this->_quoteRepository->get($quoteId);
        return $quote->getItemById($quoteItemId);
    }
}
