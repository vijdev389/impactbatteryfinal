<?php

namespace ITA\Pathfinder\Model\ResourceModel;

class Customfinder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ita_custompathfinder', 'entity_id');
    }
}