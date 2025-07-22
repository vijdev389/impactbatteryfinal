<?php
/**
 * Google Tag Manager Pro block
 *
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GoogleTagManagerPro\Block;

use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template\Context;
use Scommerce\GoogleTagManagerPro\Helper\Data;
use Magento\Framework\View\Element\Template;
use Scommerce\TrackingBase\Helper\Data as TrackingBaseHelper;

class Gtm extends Template
{
    /**
     * @var Data
     */
    protected $_gtmpData;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @var TrackingBaseHelper
     */
    protected $_trackingHelper;

    /**
     * Gtm constructor.
     * @param Context $context
     * @param Data $gtmpData
     * @param Manager $moduleManager
     * @param TrackingBaseHelper $trackingHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $gtmpData,
        Manager $moduleManager,
        TrackingBaseHelper $trackingHelper,
        array $data = []
    ) {
        $this->_gtmpData = $gtmpData;
		$this->_moduleManager = $moduleManager;
		$this->_trackingHelper = $trackingHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render block html if Google Tag Manager is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getHelper()->isEnabled()
            && $this->_moduleManager->isEnabled('Scommerce_TrackingBase')
            && $this->_trackingHelper->isEnabled() ?
            parent::_toHtml() : '';
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->_gtmpData;
    }

    /**
     * @return int
     */
    public function getRemarketingType()
    {
        return $this->getHelper()->isOtherSiteEnabled() ? 1 : 0;
    }

    /**
     * @return int
     */
    public function sendEcommCategoryPath()
    {
        return $this->getHelper()->sendEcommCategoryPath() ? 1 : 0;
    }

    public function isEnhancedConversionEnabled()
    {
        return $this->_trackingHelper->isEnhancedConversionEnabled();
    }
}
