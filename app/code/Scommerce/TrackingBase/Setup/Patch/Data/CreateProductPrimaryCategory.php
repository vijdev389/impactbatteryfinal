<?php
/**
 * Create primary category product attribute data patch
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

declare(strict_types=1);

namespace Scommerce\TrackingBase\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Scommerce\TrackingBase\Model\Entity\Attribute\Source\Categories;

/**
 * Class CreateProductPrimaryCategory
 * @package Scommerce\TrackingBase\Setup\Patch\Data
 */
class CreateProductPrimaryCategory implements DataPatchInterface
{
    const ATTRIBUTE_CODE = 'product_primary_category';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * CreateProductPrimaryCategory constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply(): void
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if (!$this->isProductAttributeExists(self::ATTRIBUTE_CODE)) {
            $eavSetup->addAttribute(
                'catalog_product',
                self::ATTRIBUTE_CODE,
                [
                    'type' => 'int',
                    'label' => 'Product Primary Category',
                    'input' => 'select',
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'user_defined' => true,
                    'required' => false,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'source' => Categories::class,
                    'group' => 'Primary Category',
                ]
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @param $field
     * @return bool
     * @throws LocalizedException
     */
    protected function isProductAttributeExists($field)
    {
        $attr = $this->eavConfig->getAttribute(Product::ENTITY, $field);
        return $attr && $attr->getId();
    }
}
