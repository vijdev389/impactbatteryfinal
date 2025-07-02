<?php

/**
 * Copyright © 2020 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GoogleTagManagerPro\Model\Session;

use Magento\Framework\Session\Storage as MagentoStorage;

/**
 * Class Storage
 * @package Scommerce\GoogleTagManagerPro\Model\Session
 */
class Storage extends MagentoStorage
{
    /**
     * Storage constructor.
     * @param string $namespace
     * @param array $data
     */
    public function __construct(
        $namespace = 'sc_gtm_session',
        array $data = []
    ) {
        parent::__construct($namespace, $data);
    }
}
