<?php

namespace Rock\CategoryLayout\Observer;

use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class CategoryLayout implements ObserverInterface
{
    const ACTION_NAME = 'catalog_category_view';

    /** @var Registry */
    private $registry;

    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function execute(Observer $observer)
    {
        if ($observer->getFullActionName() !== self::ACTION_NAME) {
            return;
        }

        $category = $this->registry->registry('current_category');

        /** @var \Magento\Framework\View\Layout $layout */
        if($category->getProductBottom()) {
            // echo "test";
            // exit();
           $layout = $observer->getLayout();
            $layout->getUpdate()->addHandle('catalog_category_custom_view'); 
        }
        
    }
}