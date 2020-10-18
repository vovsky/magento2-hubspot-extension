<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\RestOutput;

use Groove\Hubshoply\Model\UrlResolver;
use Magento\Framework\Webapi\Request;

class Order implements ProcessorInterface
{
    private const KEY_REPLACEMENT_MAP = ['items' => 'order_items', 'status_histories' => 'order_comments'];

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * Order constructor.
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
        if (trim($request->getRequestUri(), '/') == trim(UrlResolver::OLD_ORDER_API_ENDPOINT, '/')) {
            if (!empty($outputData['items'])) {
                foreach ($outputData['items'] as &$item) {
                    $this->processOrder($item);
                }

                return $outputData;
            }
        } elseif (strstr($request->getRequestUri(), UrlResolver::OLD_ORDER_API_ENDPOINT) !== false) {
            return $this->processOrder($outputData);
        }

        return null;
    }

    /**
     * @param $outputData
     *
     * @return mixed
     */
    private function processOrder(&$outputData)
    {
        foreach (self::KEY_REPLACEMENT_MAP as $sourceKey => $targetKey) {
            $this->dataHelper->replaceKeys($sourceKey, $targetKey, $outputData);
        }
        $this->dataHelper->formatPricesRelatedData($outputData);
        $this->prepareAddresses($outputData);

        return $outputData;
    }

    /**
     * @param $outputData
     */
    private function prepareAddresses(&$outputData)
    {
        $outputData['addresses'] = [];
        if (isset($outputData['billing_address'])) {
            $outputData['billing_address']['street'] = $this->prepareStreet($outputData['billing_address']['street']);
            $outputData['addresses'][] = $outputData['billing_address'];
            unset($outputData['billing_address']);
        }

        if (isset($outputData['extension_attributes']['shipping_assignments'])) {
            foreach ($outputData['extension_attributes']['shipping_assignments'] as &$assignment) {
                if (isset($assignment['shipping']['address'])) {
                    $assignment['shipping']['address']['street'] = $this->prepareStreet(
                        $assignment['shipping']['address']['street']
                    );
                    $outputData['addresses'][] = $assignment['shipping']['address'];
                    break;
                }
            }
        }
    }

    /**
     * @param $street
     *
     * @return string
     */
    private function prepareStreet($street)
    {
        if (is_array($street)) {
            return implode(PHP_EOL, $street);
        }

        return $street;
    }
}
