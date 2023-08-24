<?php
namespace Rock\CategoryLayout\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'product_bottom',
            [
                'type' => 'int',
                'label' => 'Enable Product Bottom',
                'input' => 'boolean',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'visible' => true,
                'default' => '0',
                'required' => false,
                'sort_order' =>'200',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
                'backend' => ''
            ]
        );
    }
}