<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Queue;

use Magento\Framework\App\ResponseInterface;
use Psr\Log\LogLevel;

class Orders extends AbstractOrder
{

    protected function get(): ResponseInterface
    {
        $fields = $this->_initFields();
        $count = (int)$this->getRequest()->getParam('limit', '1');
        $offset = (int)$this->getRequest()->getParam('page', '1');
        /**
         * @var $resource \Magento\Sales\Model\ResourceModel\Order
         */
        $resource = $this->orderFactory->create();
        $adapter = $resource->getConnection();
        $select = $adapter->select()
            ->from($resource->getMainTable(), $fields)
            ->where('store_id = ?', $this->storeManager->getStore()->getStoreId())
            ->limit($count, ($offset - 1) * $count);

        $records = $adapter->fetchAll($select);
        $result = [
            'orders' => [],
            'page'   => $offset,
            'limit'  => $count,
            'total'  => 0,
            'pages'  => 0,
        ];

        if ($this->_joinAddressFieldsFlag) {
            foreach ($records as &$row) {
                $row['addresses'] = $this->getAddresses($row['entity_id']);
            }
        }

        $result['orders'] = $records;
        $result['total'] = $this->getTotalOrders();
        $result['pages'] = ceil($result['total'] / $result['limit']);

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody($this->json->serialize($result));

        return $this->getResponse();
    }

    protected function logRequest()
    {
        $this->logger->log(
            LogLevel::NOTICE,
            sprintf('Request for orders from %s', $this->getRequest()->getClientIp())
        );
    }
}
