<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model;

use Groove\Hubshoply\Api\ProductAttributeMediaGalleryManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductAttributeMediaGalleryManagement implements ProductAttributeMediaGalleryManagementInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * ProductAttributeMediaGalleryManagement constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    public function getList($id)
    {
        $product = $this->productRepository->getById($id);

        return $product->getMediaGalleryEntries();
    }
}
