<?php

namespace Scommerce\TrackingBase\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\ResourceConnection;

class RemoveStep implements DataPatchInterface
{
    protected $stepsField = 'scommerce_trackingbase/checkout/steps';

    private $resourceConnection;

    private $config;

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resourceConnection,
        Config $config
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
    }


    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $table = $this->config->getMainTable();
        $connection = $this->resourceConnection->getConnection();
        $selectQuery = "select * from $table where path = '" . $this->stepsField . "'";
        $selectResult = $connection->query($selectQuery);
        foreach ($selectResult->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $configValue = json_decode($row['value'], true);
            foreach ($configValue as $configValueKey => $configValueItem) {
                unset($configValue[$configValueKey]['step']);
                if (!in_array($configValueItem['type'], [2,3])) {
                    unset($configValue[$configValueKey]);
                }
            }
            $newValue = json_encode($configValue);
            $updateQuery = "update $table set value = '$newValue' where config_id = '" . $row['config_id'] . "'";
            $updateResult = $connection->query($updateQuery);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
