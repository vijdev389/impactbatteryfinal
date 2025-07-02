<?php

namespace Scommerce\GoogleTagManagerPro\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Addtocart extends Action {

    /**
     * @var SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @param Context $context
     * @param SessionManagerInterface $coreSession
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $coreSession
    ) {
        $this->_coreSession = $coreSession;
        parent::__construct($context);
    }

    /**
     * return add to basket product data
     *
     *
     * @return string
     */
    public function execute() {
        echo $this->_coreSession->getProductToBasket();
        die();
    }
}