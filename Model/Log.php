<?php

namespace Groove\Hubshoply\Model;

use Groove\Hubshoply\Api\Data\LogInterface;
use Groove\Hubshoply\Model\ResourceModel\Log\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Log
 *
 * @package Groove\Hubshoply\Model
 */
class Log extends AbstractModel implements
    LogInterface,
    IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'groove_hubshoply_log';

    /**
     * @var string
     */
    protected $_cacheTag    = 'groove_hubshoply_log';

    /**
     * @var string
     */
    protected $_eventPrefix = 'groove_hubshoply_log';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Log::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
