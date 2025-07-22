<?php
/**
 * Get Remove item from the cart observer
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Scommerce\TrackingBase\Helper\Data;
use Magento\Framework\Session\SessionManagerInterface;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductId;
use Scommerce\TrackingBase\Model\GetProductPrice;
use Scommerce\TrackingBase\Model\GetProductVariant;

/**
 * Class CheckoutRemoveItemAfter
 * @package Scommerce\TrackingBase\Observer
 */
class CheckoutRemoveItemAfter implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

	/**
     * @var SessionManagerInterface
     */
	protected $_coreSession;

    /**
     * @var ProductRepositoryInterface
     */
	protected $_productRepository;

    /**
     * @var GetProductCategory
     */
	protected $_getProductCategory;

    /**
     * @var GetBrand
     */
    protected $_getBrand;

    /**
     * @var GetProductPrice
     */
    protected $_getProductPrice;

    /**
     * @var GetProductId
     */
    protected $_getProductId;

    /**
     * @var GetProductVariant
     */
    protected $_getProductVariant;

    /**
     * CheckoutRemoveItemAfter constructor.
     * @param SessionManagerInterface $coreSession
     * @param Data $helper
     * @param ProductRepositoryInterface $productRepository
     * @param GetProductCategory $getProductCategory
     * @param GetBrand $getBrand
     * @param GetProductPrice $getProductPrice
     * @param GetProductId $getProductId
     */
    public function __construct(
        SessionManagerInterface $coreSession,
        Data $helper,
        ProductRepositoryInterface $productRepository,
        GetProductCategory $getProductCategory,
        GetBrand $getBrand,
        GetProductPrice $getProductPrice,
        GetProductId $getProductId,
        GetProductVariant $getProductVariant
    ) {
        $this->_coreSession = $coreSession;
        $this->_helper = $helper;
        $this->_productRepository = $productRepository;
        $this->_getProductCategory = $getProductCategory;
        $this->_getBrand = $getBrand;
        $this->_getProductPrice = $getProductPrice;
        $this->_getProductId = $getProductId;
        $this->_getProductVariant = $getProductVariant;
    }

    /**
     * @param EventObserver $observer
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isEnhancedEcommerceEnabled() && $this->_helper->isEnabled()) {
            $quoteItem = $observer->getQuoteItem();
            $product = $quoteItem->getProduct();
            $id = $this->_getProductId->getAttributeIdValue($quoteItem->getProduct()->getId());
            $allSkus = [$id];
            if ($quoteItem->getHasChildren() && $quoteItem->getProductType() == 'configurable') {
                $children = $quoteItem->getChildren();
                if (is_array($children) && count($children)) {
                    $simpleId = $children[0]->getProduct()->getId();
                    $allSkus = array_merge($allSkus, [$this->_getProductId->getAttributeIdValue($simpleId)]);
                    if (!$this->_helper->sendParentSKU()) {
                        $id = $simpleId;
                    }
                }
            }
            $price = $this->_getProductPrice->executeByItem($quoteItem);

            $productOutBasket = [
                'id' => $this->_helper->escapeJsQuote($id),
                'name' => $this->_helper->escapeJsQuote(trim($product->getName())),
                'category' => $this->_helper->escapeJsQuote($this->_getProductCategory->execute($product)),
                'brand' => $this->_helper->escapeJsQuote($this->_getBrand->execute($product, true)),
                'price' => $price,
                'qty'=> $quoteItem->getQty(),
                'allSkus' => $allSkus,
                'list' => $this->_helper->getImpressionListFromQuoteItem($quoteItem),
                'currency' => $this->_helper->sendBaseData() ?
                    $quoteItem->getQuote()->getBaseCurrencyCode() :
                    $quoteItem->getQuote()->getQuoteCurrencyCode(),
                'variant' => $this->_getProductVariant->execute($product, $quoteItem)
            ];
			$this->_coreSession->setProductOutBasket(json_encode($productOutBasket));
        }
    }
}
