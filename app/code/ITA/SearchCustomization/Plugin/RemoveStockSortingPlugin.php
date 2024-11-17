<?php

namespace ITA\SearchCustomization\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Select;

class RemoveStockSortingPlugin
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Around method to alter sorting of the product collection.
     *
     * @param ProductCollection $subject
     * @param \Closure $proceed
     * @param mixed ...$args
     * @return ProductCollection
     */
    public function aroundSetOrder(
        ProductCollection $subject,
        \Closure $proceed,
        $attribute,
        $dir = 'desc'
    ) {
        //$subject->getSelect()->reset(Zend_Db_Select::ORDER);
        $this->logger->info('Setting product collection sort order', ['attribute' => $attribute]);

        
        // Check if the sorting field is is_in_stock or entity_id
        if ($attribute === 'is_out_of_stock' || $attribute === 'entity_id') {
            // Reset to default sorting: sort by relevance (if applicable) and then entity_id
            $subject->getSelect()->reset(Select::ORDER); // Reset existing order
             // Only add 'relevance_score' if it exists in the collection
            if (in_array('relevance_score', $subject->getSelect()->getPart(Select::COLUMNS))) {
                $subject->getSelect()->order('relevance_score DESC');
            }

            // Sort by entity_id ascending as a fallback
            $subject->getSelect()->order('entity_id ASC');
        } else {
            // Proceed with the original method for other fields
            return $proceed($attribute, $dir);
        }

        return $this; // Return the subject for method chaining
    }
}
