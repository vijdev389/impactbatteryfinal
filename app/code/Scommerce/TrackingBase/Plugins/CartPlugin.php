<?php
/**
 * Plugin for reordering
 *
 * @category Scommerce
 * @package Scommerce_TrackingBase
 * @author Scommerce Mage <core@scommerce-mage.com>
 */

namespace Scommerce\TrackingBase\Plugins;

use Magento\Framework\Session\SessionManagerInterface;
use Scommerce\TrackingBase\Helper\Data;

/**
 * Class CartPlugin
 * @package Scommerce\TrackingBase\Plugin
 */
class CartPlugin
{
    const REORDER_LIST = 'Reorder';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * CartPlugin constructor.
     * @param Data $helper
     * @param SessionManagerInterface $coreSession
     */
    public function __construct(
        Data $helper,
        SessionManagerInterface $coreSession
    ) {
        $this->helper = $helper;
        $this->coreSession = $coreSession;
    }

    /**
     * @param $subject
     * @param $result
     */
    public function afterAddOrderItem($subject, $result)
    {
        if (!$this->helper->isEnabled() || !$this->helper->isEnhancedEcommerceEnabled()) return $result;

        $productToBasket = $this->coreSession->getProductToBasket();
        if ($productToBasket) {
            $productToBasket = json_decode($productToBasket, true);
            foreach ($productToBasket as &$prod) {
                $prod['list'] = self::REORDER_LIST;
                foreach ($result->getQuote()->getAllItems() as $item) {
                    if ($item->getProductId() == $prod['_realProductId']) {
                        $this->helper->setQuoteItemTrackingData($item, 'list', self::REORDER_LIST, 'parent');
                    }
                }
            }
            $this->coreSession->setProductToBasket(json_encode($productToBasket));
        }
        return $result;
    }
}
