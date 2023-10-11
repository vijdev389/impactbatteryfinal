<?php
/**
 * Get Category Path Service Model
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Api\Data\CategoryInterface;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class GetCategoryPath
 * @package app\code\Scommerce\TrackingBase\Model
 */
class GetCategoryPath
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     * GetCategoryPath constructor.
     * @param Data $helper
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Data $helper,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->_helper = $helper;
        $this->_categoryRepository = $categoryRepository;
    }

    /**
     * @param $category
     * @param false $both
     * @return array|string|null
     */
    public function execute($category, $both = false)
    {
        if (!$both) {
            return $this->getCategoryName($category, $this->_helper->sendCategoryPath());
        } else {
            return  [
                'full' => $this->getCategoryName($category, true),
                'plain' => $this->getCategoryName($category, false)
            ];
        }
    }

    /**
     * @param $category
     * @param $withPath
     * @return string|null
     */
    protected function getCategoryName($category, $withPath)
    {
        if (!($category instanceof Category)) {
            $category = $this->getCategory($category);
        }
        if (!$category || !$category->getId()) {
            return '';
        }
        if (!$withPath) {
            return trim($category->getName());
        }
        $result[] = trim($category->getName());
        $parent = $category->getParentCategory();
        while ($parent && $parent->getLevel() > 1) {
            if ($parent->getLevel() == 1) {
                break;
            }
            $result[] = trim($parent->getName());
            $parent = $parent->getParentCategory();
        }
        $result = array_reverse($result);
        return implode('->', $result);
    }

    /**
     * @param $categoryId
     * @return CategoryInterface|null
     */
    protected function getCategory($categoryId)
    {
        try {
            return $this->_categoryRepository->get($categoryId);
        } catch (\Exception $e) {
            return null;
        }
    }
}
