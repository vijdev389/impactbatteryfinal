<?php
/**
 * Get View Model for onepage checkout page
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\ViewModel\Checkout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Scommerce\TrackingBase\Model\GetProductId;
use Magento\Framework\Serialize\Serializer\Json;
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
use Scommerce\TrackingBase\Model\GetProductVariant;
use Scommerce\TrackingBase\Model\Source\StepType;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;

/**
 * Class Cart
 * @package Scommerce\TrackingBase\ViewModel
 */
class Onepage extends DataObject implements ArgumentInterface
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
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * @var GetProductId
     */
    protected $_getProductId;

    /**
     * @var GetProductVariant
     */
    protected $_getProductVariant;

    /**
     * @var Json
     */
    protected $_serializer;

    protected $_paymentMethods;

    protected $coupon;
    protected $salesRule;

    private $_stepsConfiguration = null;

    /**
     * @param Data $helper
     * @param GetProductCategory $getCategory
     * @param GetBrand $getBrand
     * @param GetProductPrice $getProductPrice
     * @param GetCategoryPath $getCategoryPath
     * @param GetProductCategory $getProductCategory
     * @param CheckoutSession $checkoutSession
     * @param GetParentProduct $getParentProduct
     * @param PriceHelper $priceHelper
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
        PriceHelper $priceHelper,
        GetProductId $getProductId,
        GetProductVariant $getProductVariant,
        Json $serializer,
        MethodList $paymentMethods,
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
        $this->_priceHelper = $priceHelper;
        $this->_getProductId = $getProductId;
        $this->_getProductVariant = $getProductVariant;
        $this->_serializer = $serializer;
        $this->_paymentMethods = $paymentMethods;
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
     * Returns product data
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductsData()
    {
        $_products = [];
        $index = 1;
        foreach ($this->getCartItems() as $_quoteItem) {
            /** @var $_quoteItem Item */
            if ($_quoteItem->getParentItemId()) {
                continue;
            }
            list($id, $allSkus, $_realId) = $this->_getProductId->getIdsForItem($_quoteItem);
            $brand = $this->_getBrand->execute($_quoteItem->getProduct(), true);
            $name = $_quoteItem->getProduct()->getName();
            $category = $this->_getProductCategory->execute($_quoteItem->getProduct());
            $price = $this->_getProductPrice->executeByItem($_quoteItem);
            if ($_quoteItem->getDiscountAmount() <> 0) {
                if ($this->_helper->sendBaseData()) {
                    $price = $_quoteItem->getBasePrice() - $_quoteItem->getBaseDiscountAmount() / $_quoteItem->getQty();
                } else {
                    $price = ($_quoteItem->getRowTotal() - $_quoteItem->getDiscountAmount()) / $_quoteItem->getQty();
                }
            }
            $list = $this->_helper->getImpressionListFromQuoteItem($_quoteItem);
            $_products[] = [
                'name' => $this->_helper->escapeJsQuote(trim($name)),
                'id' => $this->_helper->escapeJsQuote($id),
                'price' => $price,
                'brand' => $this->_helper->escapeJsQuote($brand),
                'category' => $this->_helper->escapeJsQuote($category),
                'quantity' => $_quoteItem->getQty(),
                'allSkus' => $allSkus,
                'index' => $index,
                'list' => $list,
                'variant' => $this->_getProductVariant->execute($_quoteItem->getProduct(), $_quoteItem)
            ];
            $index++;
        }
        return $_products;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCartItems()
    {
        $quote = $this->getQuote();
        $cartItems = $quote->getAllItems();

        return $cartItems;
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
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getTotalValue()
    {
        $quote = $this->getQuote();
        if ($quote->getCouponCode()) {
            $salesRule = $this->getSalesRuleByCode($quote->getCouponCode());
            $shippingAmount = $quote->getShippingAddress()->getShippingAmount();
            $baseShippingAmount = $quote->getShippingAddress()->getBaseShippingAmount();
            $taxAmount = $quote->getShippingAddress()->getTaxAmount() + $quote->getShippingAddress()->getDiscountTaxCompensationAmount();
            $baseTaxAmount = $quote->getShippingAddress()->getBaseTaxAmount() + $quote->getShippingAddress()->getBaseDiscountTaxCompensationAmount();

            if ($salesRule && $salesRule->getApplyToShipping()) {
                $shippingAmount = $shippingAmount - $quote->getShippingAddress()->getShippingDiscountAmount();
                $baseShippingAmount = $baseShippingAmount - $quote->getShippingAddress()->getBaseShippingDiscountAmount();
            }
            $orderAmount = $quote->getGrandTotal() - $shippingAmount;
            $baseOrderAmount = $quote->getBaseGrandTotal() - $baseShippingAmount;

            $orderAmount = $this->_helper->isOrderTotalIncludedVAT() ? $orderAmount : $orderAmount - $taxAmount;
            $baseOrderAmount = $this->_helper->isOrderTotalIncludedVAT() ? $baseOrderAmount : $baseOrderAmount - $baseTaxAmount;

            $total = $this->_helper->sendBaseData() ? $baseOrderAmount : $orderAmount;
        } else {
            if ($this->_helper->sendBaseData()) {
                $total = $this->_helper->isOrderTotalIncludedVAT() ? $quote->getBaseGrandTotal() : $quote->getBaseSubtotal();
            } else {
                $total = $this->_helper->isOrderTotalIncludedVAT() ? $quote->getGrandTotal() : $quote->getSubtotal();
            }
        }
        return $total;
    }

    protected function getSalesRuleByCode($couponCode)
    {
        $ruleId = $this->coupon->loadByCode($couponCode)->getRuleId();
        $salesRule = $this->salesRule->load($ruleId);
        return $salesRule;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCoupon()
    {
        $quote = $this->getQuote();
        return $quote->getCouponCode();
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPaymentsList()
    {
        $methods = $this->_paymentMethods->getAvailableMethods($this->_checkoutSession->getQuote());
        $result = [];
        foreach ($methods as $method) {
            $result[] = [
                'code' => $method->getCode(),
                'title' => $method->getTitle()
            ];
        }
        return $result;
    }
}
