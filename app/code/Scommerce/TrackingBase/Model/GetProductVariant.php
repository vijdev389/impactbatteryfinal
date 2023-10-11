<?php

namespace Scommerce\TrackingBase\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;

class GetProductVariant
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param $product
     * @param $itemProduct
     * @return string
     * @throws NoSuchEntityException
     */
    public function execute($product, $itemProduct)
    {
        if (!$this->isConfigurable($product)) {
            return '';
        }
        $result = array();
        $itemSKU = $itemProduct->getSku();
        $item = $this->productRepository->get($itemSKU);

        /** @var Configurable $type */
        $type = $product->getTypeInstance();
        $attributes = $type->getUsedProductAttributes($product);

        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            $result[] = $attribute->getFrontendLabel() . ': ' . $item->getAttributeText($attribute->getAttributeCode());
        }

        return implode(', ', $result);
    }

    /**
     * Check if specified product is configurable
     *
     * @param Product $product
     * @return bool
     */
    public function isConfigurable($product)
    {
        return $product->getTypeId() == Configurable::TYPE_CODE;
    }
}
