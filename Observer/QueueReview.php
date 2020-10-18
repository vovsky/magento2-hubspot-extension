<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Monolog\Logger;

class QueueReview extends BaseObserver implements ObserverInterface
{

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $review = $observer->getEvent()->getDataObject();
        if (!$this->isEnabled(current($review->getStores()))) {
            return;
        }
        $this->queueItemManagement->create(
            'review',
            'create',
            [
                'review_id'          => $review->getId(),
                'created_at'         => $review->getCreatedAt(),
                'product_id'         => $review->getEntityId(),
                'customer_id'        => $review->getCustomerId(), // can be null
                'review_title'       => $review->getTitle(),
                'review_detail'      => $review->getDetail(),
                'customer_nickname'  => $review->getNickname(),
                'product_url_suffix' => $this->config->getProductUrlSuffix(current($review->getStores())),
            ],
            $review->getStoreId()
        );

        $this->logger->log(Logger::DEBUG, sprintf('Event queued: review.create(%d).', $review->getId()));
    }
}
