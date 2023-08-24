<?php
/**
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GoogleTagManagerPro\Block;

use Magento\Framework\View\Element\Template;
use Scommerce\GoogleTagManagerPro\Helper\Data;

/**
 * Class ImpressionList
 * @package Scommerce\GoogleTagManagerPro\Block
 */
class ImpressionList extends Template
{
    /** @var Data  */
    protected $helper;

    /**
     * ImpressionList constructor.
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
