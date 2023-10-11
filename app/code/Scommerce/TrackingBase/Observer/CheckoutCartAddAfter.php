<?php
/**
 * Add Product to cart observer
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Scommerce\TrackingBase\Helper\Data;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductId;
use Scommerce\TrackingBase\Model\GetProductPrice;
use Scommerce\TrackingBase\Model\GetProductVariant;

/**
 * Class CheckoutCartAddAfter
 * @package Scommerce\TrackingBase\Observer
 */
class CheckoutCartAddAfter implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $_request;

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
     * @var GetBrand
     */
    protected $_getBrand;

    /**
     * @var GetProductCategory
     */
    protected $_getProductCategory;

    /**
     * @var GetProductPrice
     */
    protected $_getProductPrice;

    /**
     * @var Registry
     */
    private $_registry;

    /**
     * @var GetProductId
     */
    private $_getProductId;

    /**
     * @var GetProductVariant
     */
    private $_getProductVariant;

    /**
     * CheckoutCartAddAfter constructor.
     * @param SessionManagerInterface $coreSession
     * @param Http $request
     * @param Data $helper
     * @param ProductRepositoryInterface $productRepository
     * @param GetBrand $getBrand
     * @param GetProductCategory $getProductCategory
     * @param GetProductPrice $getProductPrice
     * @param Registry $registry
     */
    public function __construct(
        SessionManagerInterface $coreSession,
        Http $request,
        Data $helper,
        ProductRepositoryInterface $productRepository,
        GetBrand $getBrand,
        GetProductCategory $getProductCategory,
        GetProductPrice $getProductPrice,
        Registry $registry,
        GetProductId $getProductId,
        GetProductVariant $getProductVariant
    ) {
        $this->_coreSession = $coreSession;
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_productRepository = $productRepository;
        $this->_getBrand = $getBrand;
        $this->_getProductCategory = $getProductCategory;
        $this->_getProductPrice = $getProductPrice;
        $this->_registry = $registry;
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
            $qty = $this->_request->getParam('qty', 1);
            $product = $observer->getEvent()->getProduct();
            $isWishlist = $this->_request->getParam('wishlist_id')
                || $this->_registry->registry(Data::WISHLIST_REGISTRY) ? 1 : 0;
            if (!($product && $product->getId())) {
                return;
            }
            $_realId = $product->getId();
            $group = $this->_request->getParam('super_group');
            $quoteItem = $observer->getEvent()->getQuoteItem();
            if (!$qty) {
                $qty = $quoteItem->getData('qty_to_add');
            }
            list($id, $allSkus, $_realId) = $this->_getProductId->getIdsForItem($quoteItem);
            $list = $this->_request->getParam('sc_list');
            if (!$list) {
                $list = $this->_helper->getDefaultList();
            }
            $this->_helper->setQuoteItemTrackingData($quoteItem, 'list', $list);
            $brand = $this->_getBrand->execute($product, true);
            $currency = $this->_helper->sendBaseData() ?
                $quoteItem->getQuote()->getBaseCurrencyCode() :
                $quoteItem->getQuote()->getQuoteCurrencyCode();
            if (!$currency) {
                $currency = $this->_helper->getCurrency();
            }
            if (!$quoteItem->getPrice() || !$quoteItem->getPriceInclTax()) {
                $quoteItem->setData('sc_need_price_update', true);
            }

            if ($group) {
                $additionalItems = $this->getGroupedItems($group, $quoteItem, $id, $currency, $isWishlist);
                if ($additionalItems) {
                    $productToBasket = $additionalItems;
                }
            } else {
                $price = $this->_getProductPrice->execute($quoteItem);
                $productToBasket = [
                    'id' => $this->_helper->escapeJsQuote($id),
                    'name' => $this->_helper->escapeJsQuote(trim($product->getName())),
                    'category' => $this->_helper->escapeJsQuote($this->_getProductCategory->execute($product)),
                    'brand' => $this->_helper->escapeJsQuote($brand),
                    'price' => $price,
                    'qty'=> $qty,
                    'allSkus' => $allSkus,
                    'currency' => $currency,
                    '_realProductId' => $_realId,
                    '_isWishlist' => $isWishlist,
                    'variant' => $this->_getProductVariant->execute($product, $quoteItem)
                ];
                $productToBasket = [$productToBasket];
            }
            $this->setProductToBasket($productToBasket);
        }
    }

    /**
     * @param $group
     * @param $quoteItem
     * @param $mainSku
     * @param $currency
     * @param $isWishlist
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function getGroupedItems($group, $quoteItem, $mainSku, $currency, $isWishlist): array
    {
        $products = [];
        foreach ($group as $id => $qty) {
            if (!$qty) continue;

            $_pr = $this->_productRepository->getById($id);
            $cat = $this->_getProductCategory->execute($_pr);
            $brand = $this->_getBrand->execute($_pr);
            $id = $this->_getProductId->execute($_pr);
            $price = $this->_getProductPrice->execute($_pr);
            $products[] = [
                'id' => $id,
                'name' => trim($_pr->getName()),
                'category' => $cat,
                'brand' => $brand,
                'price' => $price,
                'qty'=> $qty,
                'currency' => $currency,
                'allSkus' => [$id, $mainSku],
                '_realProductId' => $_pr->getId(),
                '_isWishlist' => $isWishlist,
                'variant' => ''
            ];
        }
        return $products;
    }

    /**
     * @param $product
     */
    protected function setProductToBasket($product)
    {
        $existingData = $this->_coreSession->getProductToBasket();
        if ($existingData) {
            $existingData = json_decode($existingData, true);
            $existingData = array_merge($existingData, $product);
        } else {
            $existingData = $product;
        }
        $this->_coreSession->setProductToBasket(json_encode($existingData));
    }
}
