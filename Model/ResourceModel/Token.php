<?php

namespace Groove\Hubshoply\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Token extends AbstractDb
{
    const COLUMN_EXPIRES = 'expires';
    const COLUMN_CONSUMER = 'consumer_id';
    const COLUMN_ID = 'token_id';
    const TABLE_NAME = 'hubshoply_token';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::COLUMN_ID);
    }

}
