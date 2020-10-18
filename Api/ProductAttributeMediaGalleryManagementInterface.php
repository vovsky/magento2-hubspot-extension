<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Api;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;

interface ProductAttributeMediaGalleryManagementInterface
{
    /**
     * Retrieve the list of gallery entries associated with given product
     *
     * @param string $id
     * @return ProductAttributeMediaGalleryEntryInterface[]
     */
    public function getList($id);
}
