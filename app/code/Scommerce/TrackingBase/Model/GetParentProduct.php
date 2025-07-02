<?php
/**
 * Get Parent Product Service Model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProduct;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GetParentProduct
 * @package Scommerce\TrackingBase\Model
 */
class GetParentProduct
{
    /**
     * @var ConfigurableProduct
     */
    protected $_configurableProduct;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * GetParentProduct constructor.
     * @param ConfigurableProduct $configurableProduct
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ConfigurableProduct $configurableProduct,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_configurableProduct = $configurableProduct;
        $this->_productRepository = $productRepository;
    }

    /**
     * @param $childProductId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function execute($childProductId)
    {
        $parentProduct = $this->_configurableProduct->getParentIdsByChild($childProductId);
        if (isset($parentProduct[0])){
            $_parentProduct = $this->_productRepository->getById($parentProduct[0]);
            if ($_parentProduct) {
                return $_parentProduct;
            }
        }
    }
}
