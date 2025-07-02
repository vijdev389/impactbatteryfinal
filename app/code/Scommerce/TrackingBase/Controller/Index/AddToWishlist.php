<?php

namespace Scommerce\TrackingBase\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Scommerce\TrackingBase\Model\GetBrand;
use Scommerce\TrackingBase\Model\GetProductCategory;
use Scommerce\TrackingBase\Model\GetProductId;
use Scommerce\TrackingBase\Model\GetProductPrice;
use Magento\Wishlist\Model\WishlistFactory as Wishlist;

class AddToWishlist extends Action
{
    /**
     * @var SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @var JsonFactory
     */
    protected $_jsonFactory;

    private $getBrand;

    private $getProductCategory;

    private $getProductPrice;

    private $getProductId;

    private $wishlist;

    /**
     * AddToCart constructor.
     * @param Context $context
     * @param SessionManagerInterface $coreSession
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $coreSession,
        JsonFactory $jsonFactory,
        GetBrand $getBrand,
        GetProductCategory $getProductCategory,
        GetProductPrice $getProductPrice,
        GetProductId $getProductId,
        Wishlist $wishlist
    ) {
        $this->_coreSession = $coreSession;
        $this->_jsonFactory = $jsonFactory;
        $this->getBrand = $getBrand;
        $this->getProductCategory = $getProductCategory;
        $this->getProductPrice = $getProductPrice;
        $this->getProductId = $getProductId;
        $this->wishlist = $wishlist;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->_jsonFactory->create();
        $data = [];
        $itemId = $this->_request->getParam('itemId');
        $customerId = $this->_coreSession->getVisitorData()['customer_id'];
        $wishlist = $this->wishlist->create();
        $wishlist = $wishlist->loadByCustomerId($customerId);
        $wishlistItems = $wishlist->getItemCollection()->getItems();
        if (!$wishlistItems) {
            return $result->setData($data);
        }
        foreach ($wishlistItems as $wishlistItem) {
            if ($wishlistItem->getProductId() == $itemId) {
                $item = $wishlistItem;
            }
        }
        $product = $item->getProduct();
        $cat = $this->getProductCategory->execute($product);
        $brand = $this->getBrand->execute($product);
        $id = $this->getProductId->execute($product);
        $price = $this->getProductPrice->execute($product);
        $data = [
            'value' => $price,
            'item' => [
                'id' => $id,
                'name' => trim($product->getName()),
                'category' => $cat,
                'brand' => $brand,
                'price' => $price,
                'quantity'=> $item->getQty(),
                'allSkus' => [$id],
                '_realProductId' => $product->getId(),
                'variant' => ''
            ]
        ];

        return $result->setData($data);
    }
}
