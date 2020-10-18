<?php

namespace Groove\Hubshoply\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class QueueItem
 *
 * @package Groove\Hubshoply\Model\ResourceModel
 */
class QueueItem extends AbstractDb
{
    /**
     *
     */
    const TABLE_NAME = 'hubshoply_queue_item';

    /**
     *
     */
    const COLUMN_ID = 'item_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::COLUMN_ID);
    }
}
