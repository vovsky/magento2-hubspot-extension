<?php

namespace Groove\Hubshoply\Observer;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\QueueItemManagement;
use Psr\Log\LoggerInterface;

class BaseObserver
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var QueueItemManagement
     */
    protected $queueItemManagement;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Config $config,
        QueueItemManagement $queueItemManagement,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->queueItemManagement = $queueItemManagement;
        $this->logger = $logger;
    }

    /**
     * @param $storeId
     *
     * @return bool
     */
    protected function isEnabled($storeId): bool
    {
        return $this->config->isEnabled($storeId);
    }
}
