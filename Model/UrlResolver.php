<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\PathInfo;
use Magento\Store\Model\StoreManagerInterface;

class UrlResolver
{
    const OLD_API_FRONT_NAME                 = 'api';
    const REST_API_AREA                      = 'webapi_rest';
    const OLD_API_ENDPOINT                   = "/api/rest/";
    const NEW_API_ENDPOINT                   = "/rest/V1/";
    const NEW_API_ENDPOINT_STORE_CODE_HOLDER = "/rest/{store_code}/V1/";
    const OLD_PRODUCTS_API_ENDPOINT          = self::OLD_API_ENDPOINT . 'products';
    const OLD_ORDER_API_ENDPOINT             = self::OLD_API_ENDPOINT . 'orders';

    /**
     * @var Http
     */
    private $http;

    /**
     * @var PathInfo
     */
    private $pathInfoService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(Http $http, PathInfo $pathInfoService, StoreManagerInterface $storeManager)
    {
        $this->http = $http;
        $this->pathInfoService = $pathInfoService;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function replacePath(string $url): string
    {
        if (strstr($url, self::OLD_API_ENDPOINT) !== false) {
            $fromRequest = $this->pathInfoService->getPathInfo($this->http->getRequestUri(), $this->http->getBaseUrl());
            $diff = array_diff($this->explode($fromRequest), $this->explode($url));
            if ($diff) {
                $stores = $this->storeManager->getStores(false, true);

                foreach ($diff as $pathPart) {
                    if (isset($stores[$pathPart])) {
                        $replaceString = str_replace(
                            '{store_code}',
                            $pathPart,
                            self::NEW_API_ENDPOINT_STORE_CODE_HOLDER
                        );

                        return str_replace(self::OLD_API_ENDPOINT, $replaceString, $url);
                    }
                }
            }

            return str_replace(self::OLD_API_ENDPOINT, self::NEW_API_ENDPOINT, $url);
        }

        return $url;
    }

    private function explode(string $path)
    {
        $path = explode('/', trim($path, "/"));

        return $path;
    }
}
