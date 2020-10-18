<?php

namespace Groove\Hubshoply\Model;

use Groove\Hubshoply\Api\Data\AbandonedCartInterface;
use Groove\Hubshoply\Model\ResourceModel\AbandonedCart\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @method ResourceModel\AbandonedCart getResource()
 * @method Collection getCollection()
 */
class AbandonedCart extends AbstractModel implements
    AbandonedCartInterface,
    IdentityInterface
{

    const CACHE_TAG = 'groove_hubshoply_abandonedcart';

    /**
     * @var string
     */
    protected $_cacheTag    = 'groove_hubshoply_abandonedcart';

    /**
     * @var string
     */
    protected $_eventPrefix = 'groove_hubshoply_abandonedcart';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\AbandonedCart::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $quote_id
     * @param $store_id
     *
     * @return $this
     */
    public function loadByQuoteStore($quote_id, $store_id)
    {
        $id = $this->getCollection()
            ->addFieldToFilter('quote_id', $quote_id)
            ->addFieldToFilter('store_id', $store_id)
            ->getFirstItem()->getId();

        if ($id) {
            $this->load($id);
        }

        return $this;
    }
}
