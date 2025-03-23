<?php

namespace Rocktechnolabs\MetaDescription\Observer;

use Magento\Framework\Event\ObserverInterface;

class SeoMetaObserver implements ObserverInterface
{
    const XML_PRODUCT_AUTO_METADESCRIPTION = 'catalog/fields_masks/meta_description';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * SeoMetaObserver constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        
        $metaDesc = trim((string) $product->getAttributeDefaultValue('meta_description'));
        if ($metaDesc == '') {
            $metaDesc = trim((string) $product->setStore(1)->getMetaDescription());
        }
        // $metaDesc = "test Working .....";
        $product->setMetaDescription($metaDesc);
    }
}