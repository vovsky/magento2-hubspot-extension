<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class SalesOrderBeforeSaveObserver extends BaseObserver implements ObserverInterface
{
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$this->isEnabled($order->getStoreId())) {
            return;
        }

        if ($order->isObjectNew()) {
            $order->setObjectNewTmp(true);
        }
    }
}
