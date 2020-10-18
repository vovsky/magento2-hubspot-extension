<?php

namespace Groove\Hubshoply\Model\ResourceModel\QueueItem;

use Groove\Hubshoply\Model\QueueItem;
use Groove\Hubshoply\Model\ResourceModel\QueueItem as QueueItemResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Groove\Hubshoply\Model\ResourceModel\QueueItem
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    protected function _construct()
    {
        $this->_init(QueueItem::class, QueueItemResource::class);
    }

}
