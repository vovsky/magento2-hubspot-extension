<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Observer;

use DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Monolog\Logger;
use Psr\Log\LogLevel;

class SalesOrderAfterSaveObserver extends BaseObserver implements ObserverInterface
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

        $payload = [
            'order_id'           => $order->getId(),
            'product_url_suffix' => $this->config->getProductUrlSuffix($order->getStoreId()),
        ];

        if ($order->getObjectNewTmp()) {
            $payload = $payload + ['created_at' => date(DateTime::W3C, strtotime($order->getCreatedAt()))];

            $this->queueItemManagement->create('order', 'created', $payload, (string)$order->getStoreId());

            $this->logger->log(LogLevel::DEBUG, sprintf('Event queued: order.created(%d).', $order->getId()));

            $order->setObjectNewTmp(false);
        } else {
            if ($order->getCreatedAt() != $order->getUpdatedAt()) {
                $payload = $payload +
                    ['updated_at' => date(DateTime::W3C, strtotime($order->getUpdatedAt()))];

                $this->queueItemManagement->create('order', 'updated', $payload, (string)$order->getStoreId());

                $this->logger->log(
                    Logger::DEBUG,
                    sprintf('Event queued: order.updated(%d).', $order->getId())
                );
            }
        }
    }
}
