<?php

namespace Scommerce\GoogleTagManagerPro\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Order
 * @package Scommerce\GoogleTagManagerPro\Controller\Index
 */
class Order extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Order constructor.
     * @param PageFactory $pageFactory
     * @param Context $context
     */
    public function __construct(
        PageFactory $pageFactory,
        Context $context
    ) {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = $this->pageFactory->create();
        return $result;
    }
}
