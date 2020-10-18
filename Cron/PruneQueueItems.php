<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Cron;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\ResourceModel\QueueItem\Collection;
use Groove\Hubshoply\Model\ResourceModel\QueueItem\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class PruneQueueItems
 *
 * @package Groove\Hubshoply\Cron
 */
class PruneQueueItems
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var int
     */
    private $staleLengthInDays;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PruneQueueItems constructor.
     *
     * @param Config            $config
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface   $logger
     * @param int               $staleLengthInDays
     */
    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        int $staleLengthInDays = 30
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->staleLengthInDays = $staleLengthInDays;
        $this->logger = $logger;
    }

    /**
     *
     */
    public function execute()
    {
        if ($this->config->getActiveStores()) {
            //get all queue items with an additional column for age
            /**
             * @var $queueitems Collection
             */
            $queueitems = $this->collectionFactory->create();

            $queueitems->getSelect()
                ->columns(['date_diff_queue_age' => new \Zend_Db_Expr('DATEDIFF(NOW(),created_at)')])
                ->having('date_diff_queue_age >= ?', $this->staleLengthInDays);

            $total = count($queueitems);

            $queueitems->walk('delete');

            $this->logger->info(sprintf('Pruned %d stale queue items.', $total));
        }
    }
}
