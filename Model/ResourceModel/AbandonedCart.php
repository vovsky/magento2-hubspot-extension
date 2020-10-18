<?php

namespace Groove\Hubshoply\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AbandonedCart extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('hubshoply_abandonedcart', 'cart_id');
    }
}
