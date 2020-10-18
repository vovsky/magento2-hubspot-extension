<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Queue;

use Groove\Hubshoply\Api\Data\TokenInterfaceFactory;
use Groove\Hubshoply\Helper\Error;
use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\QueueItem;
use Groove\Hubshoply\Model\ResourceModel\QueueItem\Collection;
use Groove\Hubshoply\Model\ResourceModel\QueueItem\CollectionFactory;
use Groove\Hubshoply\Request\QueueViewProcessor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;

/**
 * Class View
 *
 * @package Groove\Hubshoply\Controller\Queue
 */
class View extends AbstractQueue
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var QueueViewProcessor
     */
    private $queueViewProcessor;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * View constructor.
     *
     * @param Context               $context
     * @param LoggerInterface       $logger
     * @param TokenInterfaceFactory $tokenInterfaceFactory
     * @param Config                $config
     * @param StoreManager          $storeManager
     * @param Error                 $error
     * @param Json                  $json
     * @param QueueViewProcessor    $queueViewProcessor
     * @param CollectionFactory     $collectionFactory
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TokenInterfaceFactory $tokenInterfaceFactory,
        Config $config,
        StoreManager $storeManager,
        Error $error,
        Json $json,
        QueueViewProcessor $queueViewProcessor,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $logger, $tokenInterfaceFactory, $config, $storeManager, $error);
        $this->json = $json;
        $this->queueViewProcessor = $queueViewProcessor;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return ResponseInterface
     */
    public function get(): ResponseInterface
    {
        $request = $this->getRequest();
        /**
         * @var $collection Collection
         */
        $collection = $this->collectionFactory->create();
        $this->queueViewProcessor->process($request, $collection);
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody($this->json->serialize(
                [
                    'queue_items' => $this->prepareItems($collection),
                    'count'       => $collection->count()
                ]
            ));

        return $this->getResponse();
    }

    /**
     * @return mixed|void
     */
    protected function logRequest()
    {
        $this->logger->info(
            sprintf('Request to view queue from %s', $this->getRequest()->getClientIp())
        );
    }

    /**
     * @param $collection
     *
     * @return array
     */
    private function prepareItems($collection): array
    {
        $result = [];
        /**
         * @var $item QueueItem
         */
        foreach ($collection as $item) {
            $result[] = [
                "queue_item_id" => $item->getQueueItemId(),
                "event_entity"  => $item->getEventType(),
                "event_type"    => $item->getEventEntity(),
                "payload"       => $this->json->unserialize($item->getPayloadJson()),
                "created_at"    => $item->getCreatedAt(),
                "store_id"      => $item->getStoreId()
            ];
        }

        return $result;
    }
}