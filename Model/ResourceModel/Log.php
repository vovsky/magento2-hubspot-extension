<?php

namespace Groove\Hubshoply\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Log
 *
 * @package Groove\Hubshoply\Model\ResourceModel
 */
class Log extends AbstractDb
{

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('hubshoply_log', 'log_id');
    }

}
