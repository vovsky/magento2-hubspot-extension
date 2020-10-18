<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Api;

use Groove\Hubshoply\Api\Data\QueueItemInterface;

interface QueueItemManagementInterface
{
    /**
     * @param string $event
     * @param string $entity
     * @param string $storeId
     * @param array  $payload
     *
     * @return mixed
     */
    public function create(string $entity, string $event, array $payload, string $storeId): QueueItemInterface;

    /**
     * @param string $from
     * @param string $to
     *
     * @return mixed
     */
    public function delete(string $from, string $to);
}
