<?php 
namespace Rock\StockStatus\Plugin\Ui\DataProvider\Product;

class ProductDataProvider
{
    /**
     * @param \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @return mixed
     */
    public function afterGetCollection(
        \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject,
        $collection
    ) {
        $columns = $collection->getSelect()->getPart(\Zend_Db_Select::COLUMNS);
        if (!$collection->isLoaded() && !$this->checkJoin($columns)) {
            $collection->joinTable(
                'cataloginventory_stock_item',
                'product_id=entity_id',
                ["stock_status" => "is_in_stock"],
                null ,
                'left'
            )->addAttributeToSelect('is_in_stock');
        }

        return $collection;
    }

    /**
     * @param array $columns
     * @return bool
     */
    private function checkJoin($columns)
    {
        foreach ($columns as $column) {
            if(is_array($column)) {
                if(in_array('is_in_stock', $column)) {
                    return true;
                }
            }
        }

        return false;
    }
}