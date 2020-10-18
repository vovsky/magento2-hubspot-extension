<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Observer\Customer;

use DateTime;
use Exception;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Monolog\Logger;

class CreateContact extends CustomerBaseObserver implements ObserverInterface
{

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /**
         * @var $customer Customer
         */
        $customer = $observer->getEvent()->getCustomer();
        if (!$this->isEnabled($customer->getStoreId())) {
            return;
        }
        try {
            $dateTime = (new DateTime($customer->getCreatedAt()))->getTimestamp();
        } catch (Exception $e) {
            $dateTime = null;
        }
        $this->queueItemManagement->create(
            'customer',
            'create',
            [
                'email'                 => $customer->getEmail(),
                'account_creation_date' => $dateTime,
                'first_name'            => $customer->getFirstname(),
                'last_name'             => $customer->getLastname(),
                'middle_name_initial'   => $customer->getMiddlename(),
                'customer_group'        => $this->getCustomerGroupCode($customer),
            ],
            (string)$customer->getStoreId()
        );

        $this->logger->log(Logger::DEBUG, sprintf('Event queued: customer.create(%d).', $customer->getId()));
    }
}
