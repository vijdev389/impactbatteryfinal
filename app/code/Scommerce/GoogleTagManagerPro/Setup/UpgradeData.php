<?php

/**
 * Product GoogleTagManagerPro UpgradeData
 *
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GoogleTagManagerPro\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Class UpgradeData
 * @package Scommerce\GoogleTagManagerPro\Setup
 */
class UpgradeData implements UpgradeDataInterface {

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Upgrade script
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '2.0.37', '<')) {
            $attributeCode = 'product_primary_category';
            /**
             * Add attributes to the eav/attribute
             */
            $eavSetup->removeAttribute(Product::ENTITY, $attributeCode);
            $eavSetup->addAttribute(
                Product::ENTITY,
                $attributeCode,
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Primary Category',
                    'input' => 'select',
                    'class' => '',
                    'source' => 'Scommerce\GoogleTagManagerPro\Model\Entity\Attribute\Source\Categories',
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Primary Category',
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false
                ]
            );
        }

        $setup->endSetup();
    }

}
