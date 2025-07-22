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
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Data as TaxHelper;

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

    /** @var TaxHelper */
    protected $taxHelper;

    /** @var ProductRepository */
    protected $productRepository;

    protected $simpleProductTypes = ['simple', 'downloadable', 'virtual'];

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
        ProductResource $resource,
        TaxHelper $taxHelper,
        ProductRepository $productRepository
    ) {
        $this->_productHelper = $productHelper;
        $this->_priceHelper = $priceHelper;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
        $this->_taxCalculation = $taxCalculation;
        $this->_resource = $resource;
        $this->taxHelper = $taxHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * @param $product
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute($item, $refundCurrency = false)
    {
        try {
            $product = $this->productRepository->get($item->getSku());
        } catch (\Exception $e) {
            $product = $this->productRepository->get($item->getData('sku'));
        }
        if ($this->_helper->sendBaseData()) {
            $price = $this->_getPrice($product);
            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $currencyCodeFrom = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
                $currencyCodeTo = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
                if ($currencyCodeFrom !== $currencyCodeTo) {
                    $rate = $this->_currencyFactory->create()->load($currencyCodeFrom)->getAnyRate($currencyCodeTo);
                    $price = $price * $rate;
                }
            }
        } else {
            $price = $this->_getPrice($product);
            if ($refundCurrency) {
                $currencyCodeFrom = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
                if ($currencyCodeFrom !== $refundCurrency) {
                    $rate = $this->_currencyFactory->create()->load($currencyCodeFrom)->getAnyRate($refundCurrency);
                    $price = $price * $rate;
                }
            } elseif (in_array($product->getTypeId(), $this->simpleProductTypes)) {
                $price = $this->_priceHelper->currencyByStore($price, null, false);
            }
        }
        return $price === null ? number_format(0, 2) : number_format($price, 2);
    }

    public function executeBySku($sku, $refundCurrency = false)
    {
        try {
            $product = $this->productRepository->get($sku);
            return $this->execute($product, $refundCurrency);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function executeByItem($item, $refundCurrency = false)
    {
        if ($refundCurrency) {
            $price = $this->executeBySku($item->getSku(), $refundCurrency);
            if ($price === false) {
                $price = $this->executeBySku($item->getData('sku'), $refundCurrency);
            }
        } else {
            $product = $item->getProduct();
            if ($product->getTypeId() == 'configurable') {
                $price = $this->executeBySku($product->getSku(), $refundCurrency);
                if ($price === false) {
                    $price = $this->executeBySku($product->getData('sku'), $refundCurrency);
                }
            } else {
                $price = $this->executeBySku($product->getData('sku'), $refundCurrency);
            }
        }

        return $price;
    }

    /**
     * @param $product
     * @return float|int
     */
    private function _getPrice($product)
    {
        return $this->taxHelper->getTaxPrice($product, $product->getFinalPrice(), $this->_helper->isPriceIncludedTax());
    }
}
