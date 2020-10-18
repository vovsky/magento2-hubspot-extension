<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use Monolog\Logger;

class QueueNewsLetterSubscriberObserver extends BaseObserver implements ObserverInterface
{

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /* @var $subscriber Subscriber */
        $subscriber = $observer->getEvent()->getSubscriber();

        if (!$this->isEnabled($subscriber->getStoreId())) {
            return;
        }

        $changed = $observer->getEvent()->getDataObject()->isStatusChanged();
        $subscribed = $observer->getEvent()->getDataObject()->getStatus();

        if ($changed && ($subscribed == 1)) {
            $this->queueItemManagement->create(
                'newsletter',
                'subscribe',
                [
                    'email'             => $subscriber->getSubscriberEmail(),
                    'subscription_date' => time(),
                ],
                (string)$subscriber->getStoreId()
            );

            $this->logger->log(Logger::DEBUG, sprintf('Event queued: newsletter.subscribe(%s).', $subscriber));
        }
    }
}
