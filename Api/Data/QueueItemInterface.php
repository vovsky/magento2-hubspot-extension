<?php
declare(strict_types = 1);

namespace  Groove\Hubshoply\Api\Data;

/**
 * Interface QueueItemInterface
 *
 * @package Groove\Hubshoply\Api\Data
 */
interface QueueItemInterface
{
    /**
     * @return string
     */
    public function getQueueItemId(): string;


    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $event
     *
     * @return mixed
     */
    public function setEventType(string $event): QueueItemInterface;

    /**
     * @param string $entity
     *
     * @return mixed
     */
    public function setEventEntity(string $entity): QueueItemInterface;

    /**
     * @param string $store_id
     *
     * @return mixed
     */
    public function setStoreId(string $store_id): QueueItemInterface;

    /**
     * @param string $payload
     *
     * @return mixed
     */
    public function setPayloadJson(string $payload): QueueItemInterface;

    /**
     * @return string
     */
    public function getEventType(): string;

    /**
     * @return string
     */
    public function getEventEntity(): string;

    /**
     * @return string
     */
    public function getStoreId(): string;

    /**
     * @return string
     */
    public function getPayloadJson(): string;
}
