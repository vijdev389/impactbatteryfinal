<?php
/**
 * This Block is Overridden from MageWorx\SeoMarkup\Block\Head\Json\Product for Brand Details
 */

namespace Rock\Customization\Block\Head\Json;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Product extends \MageWorx\SeoMarkup\Block\Head\Json\Product
{
    const IN_STOCK     = 'http://schema.org/InStock';
    const OUT_OF_STOCK = 'http://schema.org/OutOfStock';
    const OFFER        = 'http://schema.org/Offer';

    /**
     * @var \MageWorx\SeoMarkup\Helper\Product
     */
    protected $helperProduct;

    /**
     * @var \MageWorx\SeoMarkup\Helper\DataProvider\Product
     */
    protected $helperDataProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $helperCatalog;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     *
     * @return array
     */
    protected function getJsonProductData(): array
    {
        $product = $this->getEntity();

        if (!$product) {
            $product = $this->registry->registry('current_product');
        }

        if (!$product) {
            return [];
        }

        $this->_product = $product;

        $data                = [];
        $data['@context']    = 'http://schema.org';
        $data['@type']       = 'Product';
        $data['name']        = $this->_product->getName();
        $data['description'] = $this->helperDataProvider->getDescriptionValue($this->_product);
        $data['image']       = $this->helperDataProvider->getProductImage($this->_product)->getImageUrl();

        $offers = $this->getOfferData();
        if (!empty($offers['price']) || !empty($offers[0]['price'])) {
            $data['offers'] = $offers;
        }

        $aggregateRatingData = $this->helperDataProvider->getAggregateRatingData($this->_product, false);

        if (!empty($aggregateRatingData)) {
            $aggregateRatingData['@type'] = 'AggregateRating';
            $data['aggregateRating']      = $aggregateRatingData;
        }

        /**
         * Google console error: "Either 'offers', 'review' or 'aggregateRating' should be specified"
         */
        if ($this->helperProduct->isRsEnabledForSpecificProduct() === false
            && empty($data['aggregateRating'])
            && empty($data['offers'])
        ) {
            return [];
        }

        if (!empty($data['aggregateRating']) && $this->helperProduct->isReviewsEnabled()) {
            $reviewData = $this->helperDataProvider->getReviewData($this->_product, false);

            if (!empty($reviewData)) {
                $data['review'] = $reviewData;
            }
        }

        $productIdValue = $this->helperDataProvider->getProductIdValue($this->_product);

        if ($productIdValue) {
            $data['productID'] = $productIdValue;
        }

        $color = $this->helperDataProvider->getColorValue($this->_product);
        if ($color) {
            $data['color'] = $color;
        }

        $brand = $this->helperDataProvider->getBrandValue($this->_product);
        if ($brand) {
            $brandData['@type'] = 'Brand';
            $brandData['name'] = $brand;
            $data['brand'] = $brandData;
        }

        $manufacturer = $this->helperDataProvider->getManufacturerValue($this->_product);
        if ($manufacturer) {
            $data['manufacturer'] = $manufacturer;
        }

        $model = $this->helperDataProvider->getModelValue($this->_product);
        if ($model) {
            $data['model'] = $model;
        }

        $gtin = $this->helperDataProvider->getGtinData($this->_product);
        if (!empty($gtin['gtinType']) && !empty($gtin['gtinValue'])) {
            $data[$gtin['gtinType']] = $gtin['gtinValue'];
        }

        $skuValue = $this->helperDataProvider->getSkuValue($this->_product);
        if ($skuValue) {
            $data['sku'] = $skuValue;
        }

        $weightValue = $this->helperDataProvider->getWeightValue($this->_product);
        if ($weightValue) {
            $data['weight'] = $weightValue;
        }

        $categoryName = $this->helperDataProvider->getCategoryValue($this->_product);
        if ($categoryName) {
            $data['category'] = $categoryName;
        }

        $customProperties = $this->helperProduct->getCustomProperties();

        if ($customProperties) {
            foreach ($customProperties as $propertyName => $propertyValue) {
                if (!$propertyName || !$propertyValue) {
                    continue;
                }
                $value = $this->helperDataProvider->getCustomPropertyValue($product, $propertyValue);
                if ($value) {
                    $data[$propertyName] = $value;
                }
            }
        }

        return $data;
    }
}
