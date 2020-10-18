<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\RestOutput;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Request;
use Magento\Store\Model\StoreManagerInterface;

class Image implements ProcessorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * Image constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param DataHelper            $dataHelper
     */
    public function __construct(StoreManagerInterface $storeManager, DataHelper $dataHelper)
    {
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @inheritDoc
     */
    public function process(Request $request, $outputData)
    {
        if (preg_match(
            '/products' . '\/\d+\/images' . '/',
            $request->getRequestUri()
        )) {
            if (is_array($outputData)) {
                foreach ($outputData as &$image) {
                    $this->prosessImage($image);
                }

                return $outputData;
            }
        }

        return null;
    }

    /**
     * @param $image
     *
     * @return mixed
     */
    private function prosessImage(&$image)
    {
        try {
            if ($image['file']) {
                $image['file'] = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) .
                    'catalog/product' . $image['file'];
                $this->dataHelper->replaceKeys('file', 'url', $image);
                $this->dataHelper->replaceKeys('disabled', 'exclude', $image);
                $image['exclude'] = sprintf('%b', $image['exclude']);
            }
        } catch (NoSuchEntityException $e) {
            return $image;
        }

        return $image;
    }
}