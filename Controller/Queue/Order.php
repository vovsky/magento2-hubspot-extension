<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Queue;

use Exception;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Order
 *
 * @package Groove\Hubshoply\Controller\Queue
 */
class Order extends AbstractOrder
{
    /**
     * @return ResponseInterface
     */
    protected function get(): ResponseInterface
    {
        try {
            $fields = $this->_initFields();
            $incrementId = $this->getRequest()->getParam('order_id');

            if (empty($incrementId)) {
                return $this->error->prepareResponse(
                    400,
                    'Bad Request',
                    'No order increment ID provided.'
                );
            }

            /**
             * @var $resource \Magento\Sales\Model\ResourceModel\Order
             */
            $resource = $this->orderFactory->create();
            $adapter = $resource->getConnection();
            $select = $adapter->select()
                ->from($resource->getMainTable(), $fields)
                ->where('increment_id = ?', $incrementId);

            $result = $adapter->fetchRow($select);

            if (empty($result)) {
                return $this->error->prepareResponse(
                    404,
                    'Not Found',
                    'No order for this ID could be found.'
                );
            }

            if ($this->_joinAddressFieldsFlag) {
                $result['addresses'] = $this->getAddresses($result['entity_id']);
            }
            $this->getResponse()
                ->setHeader('Content-Type', 'application/json')
                ->setBody($this->json->serialize($result));

            return $this->getResponse();
        } catch (Exception $error) {
            $this->logger->error($error);

            return $this->error->prepareResponse(
                500,
                'Failed to process order request.',
                $error->getMessage()
            );
        }
    }

    /**
     * @return mixed|void
     */
    protected function logRequest()
    {
        $this->logger->log(
            LogLevel::NOTICE,
            sprintf('Request for single order from %s', $this->getRequest()->getClientIp())
        );
    }
}
