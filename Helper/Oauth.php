<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Helper;

use Exception;
use Groove\Hubshoply\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Oauth\Consumer;
use Magento\Store\Model\StoreManagerInterface;

class Oauth
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var OauthServiceInterface
     */
    private $oauthService;

    /**
     * @var UrlBuilderInterface
     */
    private $urlBuilder;

    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Config $config,
        IntegrationServiceInterface $integrationService,
        OauthServiceInterface $oauthService,
        UrlInterface $urlBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->config = $config;
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param                                                $url
     * @param null                                           $secure
     * @param null                                           $storeId
     * @param Consumer|null $consumer
     *
     * @return string
     * @throws IntegrationException
     * @throws LocalizedException
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function buildUrl(
        $url,
        $secure = null,
        $storeId = null,
        Consumer $consumer = null
    ) {
        if ($secure === null) {
            $secure = $this->request->isSecure();
        }

        if ($storeId === true) {
            $storeId = $this->request->getParam('store');
        }

        if (!$consumer) {
            $consumer = $this->getConsumer(null, true, $storeId);
        }
        try {
            $magentoUrl = $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_LINK);
        } catch (NoSuchEntityException $e) {
            $magentoUrl = '';
        }
        // @TODO implement url with config
        $params = [
            'magento_consumer_key'    => $consumer->getKey(),
            'magento_consumer_secret' => $consumer->getSecret(),
            'magento_url'             => $magentoUrl,
            'oauth_url'               => $this->urlBuilder->getUrl(
                'hubshoply/oauthauthorize',
                ['_secure' => $secure, 'store' => $storeId]
            ),
        ];

        return preg_replace('/\?*/', '', $url) . '?' . http_build_query($params);
    }

    /**
     * @param null $name
     * @param bool $autoGenerate
     * @param null $storeId
     *
     * @return Consumer
     * @throws IntegrationException
     * @throws LocalizedException
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function getConsumer($name = null, $autoGenerate = true, $storeId = null)
    {
        if ($name === null) {
            $name = $this->config->getIntegrationName($storeId ? $storeId : $this->request->getParam('store'));
        }

        $integration = $this->integrationService->findByName($name);

        if ($integration->getId() && $integration->getConsumerId()) {
            return $this->oauthService->loadConsumer($integration->getConsumerId());
        } else {
            if ($autoGenerate) {
                try {
                    $integration = $this->integrationService->create(
                        [
                            'name'          => $this->config->getIntegrationName($storeId),
                            'endpoint'      => $this->config->getAuthUrl($storeId),
                            "all_resources" => 1
                        ]
                    );
                    $consumer = $this->oauthService->loadConsumer($integration->getConsumerId());
                } catch (Exception $error) {
                }
            }
        }

        return $consumer;
    }
}
