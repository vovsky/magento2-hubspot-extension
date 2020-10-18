<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterfaceFactory;
use Groove\Hubshoply\Model\ResourceModel\QueueItem\Collection;
use Groove\Hubshoply\Model\ResourceModel\QueueItem\CollectionFactory;

/**
 * Class Queue
 *
 * @package Groove\Hubshoply\Model\Diagnostic
 */
class Queue implements DiagnosticInterface
{

    public const NAME = 'queue';

    public const KB_ARTICLE_URL = 'http://support.hubshop.ly/magento/magento-queue-status';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DiagnosticResultInterfaceFactory
     */
    private $diagnosticResultInterfaceFactory;

    /**
     * Queue constructor.
     *
     * @param CollectionFactory                $collectionFactory
     * @param DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->diagnosticResultInterfaceFactory = $diagnosticResultInterfaceFactory;
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            Enabled::NAME => DiagnosticResultInterface::STATUS_PASS,
        ];
    }

    /**
     * @inheritDoc
     */
    public function run($storeId): DiagnosticResultInterface
    {
        /**
         * @var $result DiagnosticResultInterface
         */
        $result = $this->diagnosticResultInterfaceFactory->create();
        $result->setLabel((string)__('Queue Status'));
        /**
         * @var $queueItemCollection Collection
         */
        $queueItemCollection = $this->collectionFactory->create();
        if ($queueItemCollection->getSize() > 0) {
            $result->setStatus(DiagnosticResultInterface::STATUS_PASS)
                ->setDetails(sprintf('Currently %d items in the queue.', $queueItemCollection->getSize()));
        } else {
            $result->setStatus(DiagnosticResultInterface::STATUS_WARN)
                ->setDetails('No items in the queue.')
                ->setUrl(self::KB_ARTICLE_URL);
        }

        return $result;
    }
}
