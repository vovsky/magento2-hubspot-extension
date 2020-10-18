<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model;

use Groove\Hubshoply\Api\Data\QueueItemInterface;
use Groove\Hubshoply\Api\QueueItemManagementInterface;
use Groove\Hubshoply\Model\ResourceModel\QueueItem as QueueItemResource;
use Groove\Hubshoply\Model\ResourceModel\QueueItem\Collection;
use Groove\Hubshoply\Model\ResourceModel\QueueItem\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class QueueItemManagement
 *
 * @package Groove\Hubshoply\Model
 */
class QueueItemManagement implements QueueItemManagementInterface
{
    /**
     * @var QueueItemResource
     */
    private $queueItemResource;

    /**
     * @var QueueItemFactory
     */
    private $queueItemFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * QueueItemManagement constructor.
     *
     * @param QueueItemResource $queueItemResource
     * @param QueueItemFactory  $queueItemFactory
     * @param Json              $json
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        QueueItemResource $queueItemResource,
        QueueItemFactory $queueItemFactory,
        Json $json,
        CollectionFactory $collectionFactory
    ) {
        $this->queueItemResource = $queueItemResource;
        $this->queueItemFactory = $queueItemFactory;
        $this->json = $json;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function create(string $entity, string $event, array $payload, string $storeId): QueueItemInterface
    {
        /**
         * @var $queueItem QueueItem
         */
        $queueItem = $this->queueItemFactory->create();
        $queueItem->setEventEntity($entity);
        $queueItem->setEventType($event);
        $queueItem->setStoreId($storeId);
        $queueItem->setPayloadJson($this->json->serialize($payload));
        $this->queueItemResource->save($queueItem);

        return $queueItem;
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return mixed|void
     */
    public function delete(string $from, string $to)
    {
        /**
         * @var $collection Collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(QueueItem::FIELD_ID, ['from' => $from, 'to' => $to]);
        $collection->walk('delete');
    }
}
