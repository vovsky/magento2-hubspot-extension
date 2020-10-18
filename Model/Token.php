<?php

namespace Groove\Hubshoply\Model;

use Groove\Hubshoply\Api\Data\TokenInterface;
use Groove\Hubshoply\Model\ResourceModel\Token\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Token
 *
 * @package Groove\Hubshoply\Model
 */
class Token extends AbstractModel implements
    TokenInterface,
    IdentityInterface
{

    const CACHE_TAG = 'groove-hubshoply_token';

    /**
     * @var string
     */
    protected $_cacheTag = 'groove-hubshoply_token';

    /**
     * @var string
     */
    protected $_eventPrefix = 'groove-hubshoply_token';

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * Token constructor.
     *
     * @param Context               $context
     * @param Registry              $registry
     * @param DateTime              $dateTime
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dateTime = $dateTime;
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Token::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return strtotime($this->getExpires()) <= $this->dateTime->timestamp();
    }

    /**
     * @return string
     */
    public function getExpires(): string
    {
        return $this->getData(ResourceModel\Token::COLUMN_EXPIRES);
    }

    /**
     * @param int $expires
     *
     * @return TokenInterface
     */
    public function setExpires(int $expires): TokenInterface
    {
        return $this->setData(ResourceModel\Token::COLUMN_EXPIRES, $expires);
    }

    /**
     * @param $id
     *
     * @return Token
     */
    public function setConsumerId($id)
    {
        return $this->setData(ResourceModel\Token::COLUMN_CONSUMER, $id);
    }
}
