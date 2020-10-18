<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Queue;

use Groove\Hubshoply\Api\Data\TokenInterfaceFactory;
use Groove\Hubshoply\Helper\Error;
use Groove\Hubshoply\Model\Config;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\ResourceModel\Order\AddressFactory;
use Magento\Sales\Model\ResourceModel\OrderFactory;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

abstract class AbstractOrder extends AbstractQueue
{
    protected $_joinAddressFieldsFlag = false;

    protected $_defaultOrderFields    = [
        'entity_id',
        'increment_id',
        'customer_firstname',
        'customer_lastname',
        'customer_email'
    ];

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TokenInterfaceFactory $tokenInterfaceFactory,
        Config $config,
        StoreManager $storeManager,
        Error $error,
        OrderFactory $orderFactory,
        AddressFactory $addressFactory,
        Json $json
    ) {
        parent::__construct($context, $logger, $tokenInterfaceFactory, $config, $storeManager, $error);
        $this->orderFactory = $orderFactory;
        $this->json = $json;
        $this->addressFactory = $addressFactory;
    }

    /**
     * Initialize the requested fields from the request.
     *
     * @param string $key Optionally specify the target field key.
     *
     * @return array
     */
    protected function _initFields($key = 'fields')
    {
        $fields = $this->getRequest()->getParam('fields', []);

        if (!is_array($fields)) {
            $fields = explode(',', (string)$fields);
        }

        if (empty($fields)) {
            $fields = $this->_defaultOrderFields;
        }

        $addressesIndex = array_search('addresses', $fields);

        if ($addressesIndex !== false) {
            array_splice($fields, $addressesIndex, 1);

            $this->_joinAddressFieldsFlag = true;
        }

        if (!in_array('entity_id', $fields)) {
            array_unshift($fields, 'entity_id');
        }

        return $fields;
    }

    protected function getAddresses($orderId)
    {
        $resource = $this->addressFactory->create();
        $adapter = $resource->getConnection();
        $select = $adapter->select()
            ->from($resource->getMainTable())
            ->where('parent_id = ?', $orderId);

        return $adapter->fetchAll($select);
    }

    protected function getTotalOrders(): int
    {
        $resource = $this->orderFactory->create();
        $adapter = $resource->getConnection();
        $select = $adapter->select()
            ->from($resource->getMainTable(), [])
            ->columns(['total' => new Zend_Db_Expr('COUNT(*)')]);

        return (int)$adapter->fetchOne($select);
    }
}
