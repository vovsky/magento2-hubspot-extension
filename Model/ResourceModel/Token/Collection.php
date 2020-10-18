<?php

namespace Groove\Hubshoply\Model\ResourceModel\Token;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'token_id';

    protected function _construct()
    {
        $this->_init('Groove\Hubshoply\Model\Token', 'Groove\Hubshoply\Model\ResourceModel\Token');
    }

}
