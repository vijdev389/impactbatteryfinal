<?php
/*
 * Copyright © 2021 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\TrackingBase\Model\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Attributes
 */
class Attributes extends AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected $attributeFactory;

    /**
     * Attributes constructor.
     * @param CollectionFactory $attributeFactory
     */
    public function __construct(
        CollectionFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * return the list of product attributes for administrator to choose from
     */
    public function getAllOptions()
    {
        $attributes = $this->attributeFactory->create();
        $attributes->addFieldToFilter('is_unique', 1);
        $attributes->setOrder('frontend_label', 'ASC');

        $attributeArray[] = [
            'value' => 'entity_id',
            'label' => 'Entity ID'
        ];
        foreach($attributes as $attribute) {
            $attributeArray[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf("%s (%s)", $attribute->getDefaultFrontendLabel(), $attribute->getAttributeCode())
            ];
        }

        return $attributeArray;
    }
}
