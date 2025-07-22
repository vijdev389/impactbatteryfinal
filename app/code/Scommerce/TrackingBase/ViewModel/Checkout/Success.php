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
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;

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

    protected $coupon;
    protected $salesRule;

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
        Coupon $coupon,
        Rule $salesRule,
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
        $this->coupon = $coupon;
        $this->salesRule = $salesRule;
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
        $itemsDiscounted = false;
        $salesRule = false;
        if ($order->getCouponCode()) {
            $salesRule = $this->getSalesRuleByCode($order->getCouponCode());
            $itemsDiscounted = true;
        }
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            list($id, $allSkus, $_realId) = $this->_getProductId->getIdsForItem($item);
            $name = $item->getProduct()->getName();
            $brand = $this->_getBrand->execute($item->getProduct(), true);
            $category = $this->_getProductCategory->execute($item->getProduct());
            $quoteItem = $this->getQuoteItem($item->getQuoteItemId(), $order->getQuoteId());
            $list = $this->_helper->getImpressionListFromQuoteItem($quoteItem);
            $price = $this->_getProductPrice->executeByItem($quoteItem);
            if ($item->getDiscountAmount() <> 0) {
                if ($this->_helper->sendBaseData()) {
                    $price = $item->getBasePrice() - $item->getBaseDiscountAmount() / $item->getQtyOrdered();
                } else {
                    $price = $item->getPrice() - $item->getDiscountAmount() / $item->getQtyOrdered();
                }
            }
            $_products[] = [
                'name' => $this->_helper->escapeJsQuote(trim($name)),
                'id' => $this->_helper->escapeJsQuote($id),
                'price' => $price,
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
        if ($itemsDiscounted) {
            $shippingAmount = $order->getShippingAmount();
            $baseShippingAmount = $order->getBaseShippingAmount();
            $taxAmount = $order->getTaxAmount() + $order->getDiscountTaxCompensationAmount();
            $baseTaxAmount = $order->getBaseTaxAmount() + $order->getBaseDiscountTaxCompensationAmount();
            if ($salesRule && $salesRule->getApplyToShipping()) {
                $shippingAmount = $shippingAmount - $order->getShippingDiscountAmount();
                $baseShippingAmount = $baseShippingAmount - $order->getBaseShippingDiscountAmount();
            }
            $orderAmount = $order->getGrandTotal() - $shippingAmount;
            $baseOrderAmount = $order->getBaseGrandTotal() - $baseShippingAmount;

            $orderAmount = $this->_helper->isOrderTotalIncludedVAT() ? $orderAmount : $orderAmount - $taxAmount;
            $baseOrderAmount = $this->_helper->isOrderTotalIncludedVAT() ? $baseOrderAmount : $baseOrderAmount - $baseTaxAmount;

            $result['revenue'] = $this->_helper->sendBaseData() ? $baseOrderAmount : $orderAmount;
            $result['shipping'] = $this->_helper->sendBaseData() ? $baseShippingAmount : $shippingAmount;
        }
        return $result;
    }

    protected function getSalesRuleByCode($couponCode)
    {
        $ruleId = $this->coupon->loadByCode($couponCode)->getRuleId();
        $salesRule = $this->salesRule->load($ruleId);
        return $salesRule;
    }

    public function getConversionItems()
    {
        $order = $this->getOrder();
        $conversionItems = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $id = $this->_getProductId->execute($item);
            $qty = (int)$item->getQtyOrdered();
            if ($item->getDiscountAmount() <> 0) {
                if ($this->_helper->sendBaseData()) {
                    $baseDiscountCompensation = $item->getBaseDiscountTaxCompensationAmount();
                    $price = $item->getBasePriceInclTax() - ($item->getBaseDiscountAmount() + ($baseDiscountCompensation ?? 0))  / $qty;
                    $priceExclTax = $item->getBasePrice() - $item->getBaseDiscountAmount() / $qty;
                } else {
                    $discountCompensation = $item->getDiscountTaxCompensationAmount();
                    $price = $item->getPriceInclTax() - ($item->getDiscountAmount() + ($discountCompensation ?? 0)) / $qty;
                    $priceExclTax = $item->getPrice() - $item->getDiscountAmount() / $qty;
                }
            } else {
                if ($this->_helper->sendBaseData()) {
                    $price = $item->getBasePriceInclTax();
                    $priceExclTax = $item->getBasePrice();
                } else {
                    $price = $item->getPriceInclTax();
                    $priceExclTax = $item->getPrice();
                }
            }
            $price = number_format((float)$price,2, '.', '');
            $priceExclTax = number_format((float)$priceExclTax, 2, '.', '');

            $conversionItems[] = [
                'id' => $id,
                'price' => $price,
                'price_excl_tax' => $priceExclTax,
                'qty' => $qty
            ];
        }

        return $conversionItems;
    }

    /**
     * @return array
     */
    public function getBasicData()
    {
        $order = $this->getOrder();
        if ($this->_helper->sendBaseData()) {
            $orderCurrency = $order->getBaseCurrencyCode();
            $orderGrandTotal = $this->_helper->isOrderTotalIncludedVAT() ? $order->getBaseGrandTotal() - $order->getBaseShippingAmount() : $order->getBaseGrandTotal() - $order->getBaseTaxAmount() - $order->getBaseShippingAmount();
            if ($order->getBaseDiscountAmount() <> 0) {
                $orderGrandTotal = $order->getBaseGrandTotal();
            }
            $orderShippingTotal = $order->getBaseShippingAmount();
            $orderTax = $order->getBaseTaxAmount();
        } else {
            $orderCurrency = $order->getOrderCurrencyCode();
            $orderGrandTotal = $this->_helper->isOrderTotalIncludedVAT() ? $order->getGrandTotal() - $order->getShippingAmount() : $order->getGrandTotal() - $order->getTaxAmount() - $order->getShippingAmount();
            if ($order->getDiscountAmount() <> 0) {
                $orderGrandTotal = $order->getGrandTotal();
            }
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
            'sha256_email_address' => $emailhash,
            'address' => [
                'first_name' => $firstname,
                'sha256_first_name' => $firstnamehash,
                'last_name' => $lastname,
                'sha256_last_name' => $lastnamehash,
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
            $resultArray['sha256_phone_number'] = $phonehash;
        }
        $resultArray['currency'] = $this->_helper->sendBaseData() ? $order->getBaseCurrencyCode() : $order->getOrderCurrencyCode();
        $resultArray['transaction_id'] = $order->getIncrementId();

        if ($order->getCouponCode()) {
            $salesRule = $this->getSalesRuleByCode($order->getCouponCode());
            $shippingAmount = $order->getShippingAmount();
            $baseShippingAmount = $order->getBaseShippingAmount();
            $taxAmount = $order->getTaxAmount() + $order->getDiscountTaxCompensationAmount();
            $baseTaxAmount = $order->getBaseTaxAmount() + $order->getBaseDiscountTaxCompensationAmount();
            if ($salesRule && $salesRule->getApplyToShipping()) {
                $shippingAmount = $shippingAmount - $order->getShippingDiscountAmount();
                $baseShippingAmount = $baseShippingAmount - $order->getBaseShippingDiscountAmount();
            }
            if ($this->_helper->sendBaseData()) {
                $resultArray['revenue_with_vat_and_shipping'] = number_format((float)$order->getBaseGrandTotal(), 2, '.','');
                $resultArray['revenue_without_vat_and_shipping'] = number_format(
                    (float)$order->getBaseGrandTotal() - $baseTaxAmount - $baseShippingAmount,
                    2, '.','');
            } else {
                $resultArray['revenue_with_vat_and_shipping'] = number_format((float)$order->getGrandTotal(), 2, '.','');;
                $resultArray['revenue_without_vat_and_shipping'] = number_format(
                    (float)$order->getGrandTotal() - $taxAmount - $shippingAmount,
                    2, '.','');
            }
        } else {
            if ($this->_helper->sendBaseData()) {
                $resultArray['revenue_with_vat_and_shipping'] = number_format((float)$order->getBaseGrandTotal(), 2, '.', '');
                $resultArray['revenue_without_vat_and_shipping'] = number_format((float)$order->getBaseSubtotal(), 2, '.', '');
            } else {
                $resultArray['revenue_with_vat_and_shipping'] = number_format((float)$order->getGrandTotal(), 2, '.', '');
                $resultArray['revenue_without_vat_and_shipping'] = number_format((float)$order->getSubtotal(), 2, '.', '');
            }
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
