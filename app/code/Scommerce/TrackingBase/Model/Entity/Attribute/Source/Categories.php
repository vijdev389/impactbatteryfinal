<?php
/**
 * Category list Source Model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Model\Entity\Attribute\Source;
use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class Categories
 * @package Scommerce\TrackingBase\Model\Entity\Attribute\Source
 */
class Categories extends AbstractSource
{
    /** @var CollectionFactory */
    protected $_categoryCollectionFactory;

    /** @var Registry */
    protected $_registry;

    /** @var Data */
    protected $helper;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var array | null */
    protected $_options;

    /**
     * Categories constructor.
     * @param CollectionFactory $categoryCollectionFactory
     * @param Registry $registry
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        Registry $registry,
        Data $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_registry = $registry;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * Get the list of all categories
     *
     * @return array
     * @throws Exception
     */
    public function getCategories()
    {
        $categoryOption = [['label' => __('Please select primary category'), 'value' => '']];
        if (!$this->helper->isEnabled()) {
            return $categoryOption;
        }

        // If product exists then retrieve only associated categories
        /** @var Product $product */
        $product = $this->_registry->registry('product');

        // Retrieve excluded categories selected in system configuration settings
        // If selection found apply it to filter category collection
        $categories = $this->getPreparedCategoryCollection();

        // If product has categories then load only those categories
        $categoryIds = $product ? $product->getCategoryIds() : [];
        if (!empty($categoryIds)) {
            $categories->addAttributeToFilter('entity_id', ['in' => $categoryIds]);
        }

        // Load category collection at the end to apply all the above filters
        $categories->load();

        // Loop through loaded categories collection
        foreach ($categories as $cat) {
            /** @var Category|CategoryResource $cat */
            // Loading collection with all the parent category ids using getPathIds
            // In simple terms it will load all parent categories associated with this category
            $childCategories = $this->createCategoryCollection()
                ->addAttributeToSelect('name')
                ->setStoreId($this->storeManager->getStore()->getId())
                ->addAttributeToFilter('entity_id', ['in' => $cat->getPathIds()])
                ->addOrderField('level');

            $fullCategoryPath = [];

            // Concatenating the whole path using name instead of id
            foreach ($childCategories as $col) {
                /* @var Category|CategoryResource $col */
                if ($col->getName()) {
                    $fullCategoryPath[] = trim($col->getName());
                }
            }
            if ($fullCategoryPath) {
                $label = implode(' -> ', $fullCategoryPath);
                $categoryOption[] = ['value' => $cat->getId(), 'label' => $label];
            }
        }
        return $categoryOption;
    }

    /**
     * Get all options
     *
     * @param bool $withEmpty Just for compatibility with parent method signature
     * @param bool $defaultValues Just for compatibility with parent method signature
     * @return array
     * @throws Exception
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->getCategories();
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     * @throws Exception
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Helper for get prepared category collection
     * Get all the categories for the particular store and sorting them by level and parent_id
     *
     * @return Collection
     * @throws Exception
     */
    protected function getPreparedCategoryCollection()
    {
        return $this->createCategoryCollection()
            ->addAttributeToSelect('name')
            ->addOrderField('level')
            ->addOrderField('parent_id');
    }

    /**
     * Helper for creating instance of category collection
     *
     * @return Collection
     */
    protected function createCategoryCollection()
    {
        return $this->_categoryCollectionFactory->create();
    }
}
