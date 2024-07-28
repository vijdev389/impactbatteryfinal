<?php
declare(strict_types=1);

namespace ITA\Pathfinder\Model\ResourceModel\Customfinder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
	protected $_previewFlag;
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ITA\Pathfinder\Model\Customfinder',
           'ITA\Pathfinder\Model\ResourceModel\Customfinder');
		//$this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}