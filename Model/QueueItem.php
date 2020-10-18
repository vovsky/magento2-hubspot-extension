<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model;

use Groove\Hubshoply\Api\Data\QueueItemInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class QueueItem
 *
 * @package Groove\Hubshoply\Model
 */
class QueueItem extends AbstractModel implements QueueItemInterface
{
    /**
     *
     */
    const FIELD_ID = 'item_id';

    /**
     *
     */
    const FIELD_EVENT_TYPE = 'event_type';

    /**
     *
     */
    const FIELD_EVENT_ENTITY = 'event_entity';

    /**
     *
     */
    const FIELD_STORE_ID = 'store_id';

    /**
     *
     */
    const FIELD_PAYLOAD = 'payload';

    /**
     *
     */
    const FIELD_CREATED_AT = 'created_at';

    protected function _construct()
    {
        $this->_init(ResourceModel\QueueItem::class);
    }
    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::FIELD_CREATED_AT);
    }

    /**
     * @return string
     */
    public function getQueueItemId(): string
    {
        return (string)$this->getData(self::FIELD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEventType(string $event): QueueItemInterface
    {
       return $this->setData(self::FIELD_EVENT_TYPE, $event);
    }

    /**
     * @inheritDoc
     */
    public function setEventEntity(string $entity): QueueItemInterface
    {
        return $this->setData(self::FIELD_EVENT_ENTITY, $entity);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId(string $store_id) : QueueItemInterface
    {
        return $this->setData(self::FIELD_STORE_ID, $store_id);
    }

    /**
     * @inheritDoc
     */
    public function setPayloadJson(string $payload) : QueueItemInterface
    {
        return $this->setData(self::FIELD_PAYLOAD, $payload);
    }

    /**
     * @inheritDoc
     */
    public function getEventType(): string
    {
        return (string)$this->getData(self::FIELD_EVENT_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function getEventEntity(): string
    {
        return (string)$this->getData(self::FIELD_EVENT_ENTITY);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): string
    {
        return (string)$this->getData(self::FIELD_STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function getPayloadJson(): string
    {
        return (string)$this->getData(self::FIELD_PAYLOAD);
    }
}
