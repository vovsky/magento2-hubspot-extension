<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Observer\Customer;

use DateTime;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Monolog\Logger;

class UpdateContact extends CustomerBaseObserver implements ObserverInterface
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

        if (!$this->isEnabled($customer->getStore()->getStoreId())) {
            return;
        }

        if ($customer->getObjectNewTmp() === null) {
            $this->queueItemManagement->create(
                'customer',
                'updated',
                [
                    'customer_id'           => $customer->getId(),
                    'email'                 => $customer->getEmail(),
                    'first_name'            => $customer->getFirstname(),
                    'last_name'             => $customer->getLastname(),
                    'middle_name_initial'   => $customer->getMiddlename(),
                    'account_creation_date' => $customer->getCreatedAtTimestamp(),
                    'customer_group'        => $this->getCustomerGroupCode($customer),
                    'updated_at'            => date(DateTime::W3C, strtotime($customer->getUpdatedAt())),
                ],
                (string)$customer->getStore()->getId()
            );

            $this->logger->log(Logger::DEBUG, sprintf('Event queued: customer.updated(%d).', $customer->getId()));
        }
    }
}