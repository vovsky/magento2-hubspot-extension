<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\RestOutput;

use Groove\Hubshoply\Model\UrlResolver;
use Magento\Framework\Webapi\Request;

class Product implements ProcessorInterface
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * Product constructor.
     *
     * @param DataHelper $dataHelper
     */
    public function __construct(DataHelper $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @inheritDoc
     */
    public function process(Request $request, $outputData)
    {
        if (strstr($request->getRequestUri(), UrlResolver::OLD_PRODUCTS_API_ENDPOINT) !== false) {
            if (!empty($outputData['items'])) {
                $keys = array_column($outputData['items'], 'id');
                $values = array_map(
                    [$this, 'processProduct'],
                    array_values($outputData['items'])
                );

                return
                    array_combine(
                        $keys,
                        $values
                    );
            } elseif (isset($outputData['sku'])) {
                return $this->processProduct($outputData);
            }
        }

        return null;
    }

    /**
     * @param $product
     *
     * @return mixed
     */
    public function processProduct(&$product)
    {
        $this->processProductCustomAttributes($product);
        $this->dataHelper->formatPricesRelatedData($product);
        $this->dataHelper->replaceKeys('id', 'entity_id', $product);

        return $product;
    }

    /**
     * @param $product
     */
    private function processProductCustomAttributes(&$product)
    {
        if (isset($product['custom_attributes'])) {
            foreach ($product['custom_attributes'] as $attribute) {
                $product[$attribute['attribute_code']] = $attribute['value'];
            }
            unset($product['custom_attributes']);
        }
    }
}
