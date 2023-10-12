<?php
/**
 * Get Product Price Service Model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Model;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;
use Scommerce\TrackingBase\Helper\Data;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

/**
 * Class GetProductPrice
 * @package Scommerce\TrackingBase\Model
 */
class GetProductPrice
{
    /**
     * @var ProductHelper
     */
    protected $_productHelper;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var TaxCalculationInterface
     */
    protected $_taxCalculation;

    /**
     * @var ProductResource
     */
    protected $_resource;

    /**
     * GetProductPrice constructor.
     * @param ProductHelper $productHelper
     * @param PriceHelper $priceHelper
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param TaxCalculationInterface $taxCalculation
     * @param ProductResource $resource
     */
    public function __construct(
        ProductHelper $productHelper,
        PriceHelper $priceHelper,
        Data $helper,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        TaxCalculationInterface $taxCalculation,
        ProductResource $resource
    ) {
        $this->_productHelper = $productHelper;
        $this->_priceHelper = $priceHelper;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
        $this->_taxCalculation = $taxCalculation;
        $this->_resource = $resource;
    }

    /**
     * @param $product
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute($product)
    {
        $includeTax = $this->_helper->isPriceIncludedTax();
        if ($this->_helper->sendBaseData()) {
            if ($product instanceof Product) {
                $price = $this->_getPrice($product);
                if (!$price) {
                    $price = $product->getMinimalPrice();
                }
                $this->_priceHelper->currencyByStore($price);
                $currencyCodeTo = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
                $currencyCodeFrom = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
                if ($currencyCodeTo !== $currencyCodeFrom) {
                    $rate = $this->_currencyFactory->create()->load($currencyCodeTo)->getAnyRate($currencyCodeFrom);
                    $price = $price * $rate;
                }
            } else {
                $price = $product->getPrice();
                if ($includeTax) {
                    $price = $product->getPriceInclTax();
                }
            }
        } else {
            if ($product instanceof Product) {
                $price = $this->_getPrice($product);
            } else {
                $price = $product->getPrice();
                if ($includeTax) {
                    $price = $product->getPriceInclTax();
                }
                $currencyCodeFrom = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
                $currencyCodeTo = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
                if ($currencyCodeFrom !== $currencyCodeTo) {
                    $rate = $this->_currencyFactory->create()->load($currencyCodeTo)->getAnyRate($currencyCodeFrom);
                    $price = $price * $rate;
                }
            }
        }
        if ($price === null) {
            return number_format(0, 2);
        }
        return number_format($price, 2);
    }

    /**
     * @param $product
     * @return float|int
     */
    private function _getPrice($product)
    {
        $includeTax = $this->_helper->isPriceIncludedTax();
        if ($productRateId = $this->_resource->getAttributeRawValue($product->getId(), 'tax_class_id', $product->getStoreId())) {
            // First get base price (=price excluding tax)
            $rate = $this->_taxCalculation->getCalculatedRate($productRateId);
            $price = $product->getPriceInfo()->getPrice('final_price')->getValue();
            if ((int) $this->_helper->isSetFlag('tax/calculation/price_includes_tax')) {
                // Product price in catalog is including tax.
                $priceExcludingTax = $price / (1 + ($rate / 100));
            } else {
                // Product price in catalog is excluding tax.
                $priceExcludingTax = $price;
            }

            $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));

            return $includeTax ? $priceIncludingTax : $priceExcludingTax;
        }
        return $product->getPrice();
    }
}
