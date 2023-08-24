<?php

namespace Scommerce\GoogleTagManagerPro\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

		if (version_compare($context->getVersion(), '2.0.36') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('quote_item'),
                'google_category',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'comment' => 'Google Category Google Tag Manager Enhanced Ecommerce',
                ]
            );

			$installer->getConnection()->addColumn(
                $installer->getTable('sales_order_item'),
                'google_category',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'comment' => 'Google Category Google Tag Manager Enhanced Ecommerce',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.37') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('quote_item'),
                'sc_tracking_data',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 4000,
                    'nullable' => false,
                    'comment' => 'Tracking info',
                ]
            );
        }

        $setup->endSetup();
    }
}
