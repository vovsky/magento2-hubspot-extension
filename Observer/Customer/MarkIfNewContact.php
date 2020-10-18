<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Observer\Customer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class MarkIfNewContact extends CustomerBaseObserver implements ObserverInterface
{

    /**
     * @inheritDoc
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

        if ($customer->isObjectNew()) {
            $customer->setObjectNewTmp(true);
        }
    }
}
