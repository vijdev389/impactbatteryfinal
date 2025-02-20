<?php
namespace Rock\Customization\Plugin\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as MagentoCollectionFactory;
use \Magento\Framework\App\ResourceConnection;

/**
 * Class CollectionFactory
 * @package Rock\Customization\Plugin\Framework\View\Element\UiComponent\DataProvider
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
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($resourceConnection);  // Call parent constructor to initialize the parent class
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Add payment method title to the grid
     *
     * @param string $dataSource
     */
    public function addPaymentMethodTitle($dataSource)
    {
        // Custom logic before the parent method
        switch ($dataSource) {
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

        // Optional: add any custom modifications here, after the original functionality
        parent::addPaymentMethodTitle($dataSource);  // Optionally, call parent method for existing logic
    }
}
