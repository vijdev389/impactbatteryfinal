<?php
/**
 * Get Brand Service Model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class GetBrand
 * @package app\code\Scommerce\TrackingBase\Model
 */
class GetBrand
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var ProductResource
     */
    protected $_resource;

    /**
     * @var AttributeRepository
     */
    protected $_productAttributeRepository;

    protected $_options;

    protected $_attribute;

    /**
     * GetBrand constructor.
     * @param Data $helper
     * @param ProductRepositoryInterface $productRepository
     * @param ProductResource $resource
     * @param AttributeRepository $productAttributeRepository
     */
    public function __construct(
        Data $helper,
        ProductRepositoryInterface $productRepository,
        ProductResource $resource,
        AttributeRepository $productAttributeRepository
    ) {
        $this->_helper = $helper;
        $this->_productRepository = $productRepository;
        $this->_resource = $resource;
        $this->_productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @param $product Product
     * @param false $loadProduct
     * @return mixed|string|null
     */
    public function execute($product, $loadProduct = false)
    {
        if ($attribute = $this->_helper->getBrandDropdown()) {
            try {
                $optionId = $this->_resource->getAttributeRawValue($product->getId(), $attribute, $product->getStoreId());
                return $this->getBrandValue($optionId);
            } catch (\Exception $e) {
                return '';
            }
        }
        return $this->_helper->getBrandText();
    }

    /**
     * @param $id
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getBrandValue($id)
    {
        $attr = $this->getAttribute();
        if (!$attr) {
            return $id;
        }
        if ($attr->getSource()) {
            return $this->getValueByOption($id);
        }
        return $id;
    }

    /**
     * @return ProductAttributeInterface|AttributeInterface
     * @throws NoSuchEntityException
     */
    protected function getAttribute()
    {
        if (!$this->_attribute) {
            $this->_attribute = $this->_productAttributeRepository->get($this->_helper->getBrandDropdown());
        }
        return $this->_attribute;
    }

    /**
     * @param $id
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getValueByOption($id)
    {
        $options = $this->getOptions();
        foreach ($options as $option)
        {
            if ($option['value'] == $id) {
                return $option['label'];
            }
        }
        return '';
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function getOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->getAttribute()->getSource()->getAllOptions();
        }
        return $this->_options;
    }
}
