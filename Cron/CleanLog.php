<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Cron;

use DateInterval;
use Exception;
use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\ResourceModel\Log\Collection;
use Groove\Hubshoply\Model\ResourceModel\Log\CollectionFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class CleanLog
 *
 * @package Groove\Hubshoply\Cron
 */
class CleanLog
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * CleanLog constructor.
     *
     * @param TimezoneInterface $timezone
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        TimezoneInterface $timezone,
        CollectionFactory $collectionFactory
    ) {
        $this->timezone = $timezone;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function execute()
    {
        /**
         * @var Collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'created_at',
            [
                'lt' => $this->getLifetimeDate(),
            ]
        );

        $count = $collection->getSize();

        $collection->walk('delete');

        return sprintf('Deleted %d records.', $count);
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getLifetimeDate()
    {
        return $this->timezone
            ->date(null, null, null, false)
            ->sub(new DateInterval('PT' . Config::LOG_ENTRY_LIFETIME . 'S'))
            ->format(DateTime::DATETIME_INTERNAL_FORMAT);
    }
}
