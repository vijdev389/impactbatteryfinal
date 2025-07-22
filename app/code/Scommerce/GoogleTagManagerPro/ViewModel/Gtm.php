<?php
/**
 *
 *
 * @category
 * @package
 * @author
 */

namespace Scommerce\GoogleTagManagerPro\ViewModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Scommerce\TrackingBase\Helper\Data;
use Scommerce\TrackingBase\ViewModel\Checkout\Onepage;
use Scommerce\TrackingBase\ViewModel\Checkout\Success;
use Scommerce\TrackingBase\ViewModel\ListProduct;
use Scommerce\TrackingBase\ViewModel\TrackingDataContainer;
use Scommerce\TrackingBase\ViewModel\ViewProduct;

/**
 * Class Gtm
 * @package Scommerce\GoogleTagManagerPro\ViewModel
 */
class Gtm implements ArgumentInterface
{
    private $helper;

    /**
     * @var TrackingDataContainer
     */
    private $container;

    /**
     * @var ListProduct
     */
    private $listProduct;

    /**
     * @var ViewProduct
     */
    private $viewProduct;

    /**
     * @var Onepage
     */
    private $onepage;

    /**
     * @var Success
     */
    private $success;

    /**
     * Gtm constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Template $block
     * @return mixed
     * @throws LocalizedException
     */
    public function getPageType(Template $block)
    {
        $b = $block->getLayout()->getBlock('scommerce_tracking_data');
        return $b->getPageType();
    }
}
