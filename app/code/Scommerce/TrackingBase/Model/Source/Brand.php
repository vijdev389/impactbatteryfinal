<?php
/**
 * Attribute source model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

/**
 * Class Brand
 * @package Scommerce\TrackingBase\Model\Source
 */
class Brand extends AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected $_attributeFactory;

    /**
     * Brand constructor.
     * @param CollectionFactory $attributeFactory
     */
    public function __construct(
        CollectionFactory $attributeFactory
    ) {
        $this->_attributeFactory = $attributeFactory;
    }

    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        $attributes = $this->_attributeFactory->create();
        $attributes->setOrder('frontend_label', 'ASC');
        $attributeArray[] = ['label' => __('Please select'), 'value' => '0'];

        foreach($attributes as $attribute) {
            $attributeArray[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf("%s (%s)", $attribute->getDefaultFrontendLabel(), $attribute->getAttributeCode())
            ];
        }

        return $attributeArray;
    }
}
