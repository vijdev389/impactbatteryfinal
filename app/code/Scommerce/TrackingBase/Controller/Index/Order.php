<?php

namespace Scommerce\TrackingBase\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Order
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
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $result = $this->pageFactory->create();
        return $result;
    }
}
