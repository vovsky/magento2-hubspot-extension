<?php

namespace Groove\Hubshoply\Model\ResourceModel\Log;

use Groove\Hubshoply\Model\Log;
use Groove\Hubshoply\Model\ResourceModel\Log as LogReource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Groove\Hubshoply\Model\ResourceModel\Log
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'log_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(Log::class, LogReource::class);
    }

}
