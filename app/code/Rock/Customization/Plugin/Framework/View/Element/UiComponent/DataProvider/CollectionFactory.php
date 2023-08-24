<?php

namespace Rock\Customization\Plugin\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as MagentoCollectionFactory;
use \Magento\Framework\App\ResourceConnection;

/**
 * Class CollectionFactory
 * @package IWD\OrderGrid\Plugin\Framework\View\Element\UiComponent\DataProvider
 */
class CollectionFactory extends \IWD\CheckoutConnector\Plugin\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
{
    /**
     * @var
     */
    private $select;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string[]
     */
    private $dataSource = [
        'sales_order_invoice_grid_data_source'    => '\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult',
        'sales_order_shipment_grid_data_source'   => '\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult',
        'sales_order_creditmemo_grid_data_source' => '\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult'
    ];

    /**
     * @param $dataSource
     */
    public function addPaymentMethodTitle($dataSource)
    {
        switch ($dataSource){
            case 'sales_order_creditmemo_grid_data_source':
            case 'sales_order_shipment_grid_data_source':
            case 'sales_order_invoice_grid_data_source':
                $this->select->joinLeft(
                    ['iwd_checkout_pay' => $this->resourceConnection->getTableName('iwd_checkout_pay')],
                    'main_table.order_id = iwd_checkout_pay.order_id',
                    [
                        'iwd_checkout_pay' => 'iwd_checkout_pay.payment_method'
                    ]
                );
                break;
        }
    }
}
