<?php
/**
 * Copy config values in case old version 2.x was installed
 *
 * @category Scommerce
 * @package Scommerce_GoogleTagManagerPro
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

declare(strict_types=1);

namespace Scommerce\GoogleTagManagerPro\Setup\Patch\Data;

use Magento\Config\Model\ResourceModel\Config as StoreConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConfigCopy implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreConfig
     */
    private $config;

    protected $_gtmConfigKeys = [
        'googletagmanagerpro/general/active'                        => 'scommerce_trackingbase/general/active',
        'googletagmanagerpro/general/enhanced_ecommerce_enabled'    => 'scommerce_trackingbase/general/enhanced_ecommerce_enabled',
        'googletagmanagerpro/general/brand_dropdown'                => 'scommerce_trackingbase/general/brand_dropdown',
        'googletagmanagerpro/general/brand_text'                    => 'scommerce_trackingbase/general/brand_text',
        'googletagmanagerpro/general/category_ajax_enabled'         => 'scommerce_trackingbase/general/category_ajax_enabled',
        'googletagmanagerpro/general/send_impression_on_scroll'     => 'scommerce_trackingbase/general/send_impression_on_scroll',
        'googletagmanagerpro/general/product_item_class'            => 'scommerce_trackingbase/general/product_item_class',
        'googletagmanagerpro/general/scroll_threshold'              => 'scommerce_trackingbase/general/scroll_threshold',
        'googletagmanagerpro/general/base'                          => 'scommerce_trackingbase/general/base',
        'googletagmanagerpro/general/attribute_key'                 => 'scommerce_trackingbase/general/attribute_key',
        'googletagmanagerpro/general/send_admin_orders'             => 'scommerce_trackingbase/general/send_admin_orders',
        'googletagmanagerpro/general/admin_source'                  => 'scommerce_trackingbase/general/admin_source',
        'googletagmanagerpro/general/admin_medium'                  => 'scommerce_trackingbase/general/admin_medium',
        'googletagmanagerpro/general/order_total_include_vat'       => 'scommerce_trackingbase/general/order_total_include_vat',
        'googletagmanagerpro/general/price_including_tax'           => 'scommerce_trackingbase/general/price_including_tax',
        'googletagmanagerpro/general/send_parent_sku'               => 'scommerce_trackingbase/general/send_parent_sku',
        'googletagmanagerpro/general/send_parent_category'          => 'scommerce_trackingbase/general/send_parent_category'
    ];

    /**
     * @param ResourceConnection $resourceConnection
     * @param StoreConfig $config
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreConfig $config
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function apply(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->config->getMainTable();
        foreach ($this->_gtmConfigKeys as $gtmKey => $baseKey) {
            $sql = "select * from " . $table . " where `path`='" . $gtmKey . "'";
            $data = $connection->fetchAll($sql);
            foreach ($data as $configData) {
                $sqlTest = "select config_id from " . $table . " where path='" . $baseKey . "' and `scope`='" .
                    $configData['scope'] . "' and `scope_id`=" . $configData['scope_id'];
                $exists = $connection->fetchOne($sqlTest);
                if (!$exists) {
                    $this->config->saveConfig($baseKey, $configData['value'], $configData['scope'], $configData['scope_id']);
                }
            }
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
}
