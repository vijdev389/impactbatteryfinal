<?php
ini_set('display_errors', '1');
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
// adding bootstrap
$bootstraps = Bootstrap::create(BP, $_SERVER);

$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();

/**
 * Set the field name
 */
$field = 'product_primary_category';

/**
 * Set the entity type code
 */
$entityTypeCode = 'catalog_product';
$tablePrefix = '';
$query = "select entity_type_id from ".$tablePrefix."eav_entity_type where entity_type_code='" . $entityTypeCode ."' limit 1";

try{
    $entity_type_id = $connection->fetchOne($query);
}
catch (Exception $e){
    echo $e->getMessage();
}
$query = "select attribute_id from ".$tablePrefix."eav_attribute where attribute_code='" . $field ."' limit 1";
try{
    $attribute_id = $connection->fetchOne($query);
}
catch (Exception $e){
    echo $e->getMessage();
}


if (strlen($entity_type_id)>0 &&  strlen($attribute_id)>0){
    $query = 'insert into '.$tablePrefix.'catalog_product_entity_int 
					select distinct null,'. $attribute_id.', 0, 
					sub.product_id,sub.category_id from '.$tablePrefix.'catalog_category_product c 
					inner join (select distinct category_id,product_id from (select distinct category_id,product_id from 
					'.$tablePrefix.'catalog_category_product order by category_id desc) cp group by product_id) sub 
					on c.category_id=sub.category_id where sub.product_id not in 
					(select entity_id from '.$tablePrefix.'catalog_product_entity_int 
					where attribute_id='.$attribute_id.' and store_id=0) 
					order by c.product_id, c.category_id desc';

    /**
     * Execute the query
     */
    try{
        $totalRows = $connection->exec($query);
    }
    catch (Exception $e){
        echo $e->getMessage().'<br/><br/>';
        echo $query;
        exit;
    }
    if ($totalRows>0){
        echo 'You have '.$totalRows.' more product(s) with primary category now';
    }
    else{
        echo 'It looks like all your products have primary category set';
    }
}
else{
    echo 'Sorry, nothing to create please check entity_type_id and attribute_id';
}

