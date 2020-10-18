<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Queue;

use Exception;
use Groove\Hubshoply\Api\Data\TokenInterfaceFactory;
use Groove\Hubshoply\Helper\Error;
use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\QueueItemManagement;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpDeleteActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class Mark
 *
 * @package Groove\Hubshoply\Controller\Queue
 */
class Mark extends AbstractQueue implements HttpDeleteActionInterface
{
    /**
     * @var QueueItemManagement
     */
    private $queueItemManagement;

    /**
     * Mark constructor.
     *
     * @param Context               $context
     * @param LoggerInterface       $logger
     * @param TokenInterfaceFactory $tokenInterfaceFactory
     * @param Config                $config
     * @param StoreManager          $storeManager
     * @param Error                 $error
     * @param QueueItemManagement   $queueItemManagement
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TokenInterfaceFactory $tokenInterfaceFactory,
        Config $config,
        StoreManager $storeManager,
        Error $error,
        QueueItemManagement $queueItemManagement
    ) {
        parent::__construct($context, $logger, $tokenInterfaceFactory, $config, $storeManager, $error);
        $this->queueItemManagement = $queueItemManagement;
    }

    /**
     * @return ResponseInterface
     */
    protected function get(): ResponseInterface
    {
        $request = $this->getRequest();
        try {
            if (($id = $request->getParam('id'))) {
                $this->queueItemManagement->delete($id, $id);
            } elseif (
                ($from = $request->getParam('from')) &&
                ($to = $request->getParam('to'))
            ) {
                $this->queueItemManagement->delete($from, $to);
            } else {
                $this->error->prepareResponse(
                    400,
                    'Bad Request',
                    'URI needs to be in the format [hubshoply/mark/id/###] 
                    for single deletions or [hubshoply/mark/from/###/to/###].'
                );
            }
        } catch (Exception $e) {
            $this->logger->log(
                LogLevel::ERROR,
                sprintf('Error during  deletion of queue items  %s', $e->getMessage())
            );
            $this->error->prepareResponse(
                500,
                'Failed to process mark request.',
                $e->getMessage()
            );
        }

        return $this->getResponse();
    }

    /**
     * @return mixed|void
     */
    protected function logRequest()
    {
        $this->logger->log(
            LogLevel::NOTICE,
            sprintf('Request to delete queue items from %s', $this->getRequest()->getClientIp())
        );
    }
}
