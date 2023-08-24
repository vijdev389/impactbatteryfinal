<?php

namespace Rock\Customization\Observer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * AddressBeforePlace Class
 */
class AddressBeforePlace implements ObserverInterface
{
    /**
     * AddressBeforePlace constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }
    
    public function execute(EventObserver $observer) {
        try {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/addressbeforeplace.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('-------------------');
            $order = $observer->getOrder();
            $logger->info("Order Increment Id: ".$order->getIncrementId());
            $logger->info("Shipping Street Line1: ".$order->getShippingAddress()->getStreetLine(1));
            $logger->info("Shipping Street Line2: ".$order->getShippingAddress()->getStreetLine(2));
            $logger->info("Shipping Zipcode: ".$order->getShippingAddress()->getPostcode());
            $logger->info('-------------------');
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
