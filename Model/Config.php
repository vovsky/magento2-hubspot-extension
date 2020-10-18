<?php

declare(strict_types = 1);

namespace Groove\Hubshoply\Model;

use Exception;
use Magento\Backend\Model\Url;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\Template;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Config
{
    const DEFAULT_MAX_CART_AGE_DAYS = 60;
    const LOG_ENTRY_LIFETIME        = 604800;
    const OAUTH_CONSUMER            = 'HubShop.ly';
    const REMOTE_AUTH_URL           = 'https://magento.hubshop.ly/auth/magento';
    const REMOTE_TEST_AUTH_URL      = 'https://hubshoply-magento-staging.herokuapp.com/auth/magento';
    const ROLE_NAME                 = 'HubShop.ly';
    const TRACKING_SCRIPT_URI       = '//magento.hubshop.ly/shops';
    const TRACKING_SCRIPT_TEST_URI  = '//hubshoply-magento-staging.herokuapp.com/shops';

    const XML_CONFIG_PATH_ADMIN_URL        = 'hubshoply/advanced/admin_url';
    const XML_CONFIG_PATH_DIAGNOSTIC_TESTS = 'global/hubshoply/diagnostic/tests';
    const XML_CONFIG_PATH_ENABLED          = 'hubshoply/advanced/enabled';
    const XML_CONFIG_PATH_FRONTEND_URL     = 'hubshoply/advanced/frontend_url';
    const XML_CONFIG_PATH_SITE_ID          = 'hubshoply/advanced/site_id';
    const XML_CONFIG_PATH_TEST_MODE        = 'hubshoply/advanced/test_mode';
    const XML_CONFIG_PATH_TRACK_CUSTOMERS  = 'hubshoply/advanced/track_customers';
    const XML_CONFIG_PATH_USER_CONFIG      = 'hubshoply/advanced/user_config';

    protected $_eventPrefix = 'hubshoply_config'; // Observe after-load to add custom config

    protected $_options     = [];

    /**
     * @var array|null
     */
    private $userConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Http
     */
    private $http;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Template
     */
    private $template;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Url
     */
    private $backEndUrl;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface   $scopeConfig
     * @param Http                   $http
     * @param StoreManager           $storeManager
     * @param \Magento\Framework\Url $url
     * @param Registry               $registry
     * @param DataObjectFactory      $dataObjectFactory
     * @param Template               $template
     * @param LoggerInterface        $logger
     * @param Url                    $backEndUrl
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Http $http,
        StoreManager $storeManager,
        \Magento\Framework\Url $url,
        Registry $registry,
        DataObjectFactory $dataObjectFactory,
        Template $template,
        LoggerInterface $logger,
        Url $backEndUrl
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->http = $http;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->registry = $registry;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->template = $template;
        $this->logger = $logger;
        $this->backEndUrl = $backEndUrl;
    }

    /**
     * @param null $storeId
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getUserConfig($storeId = null): array
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        if (!is_array($this->userConfig) || !isset($this->userConfig[$storeId])) {
            $dataString = $this->scopeConfig->getValue(
                self::XML_CONFIG_PATH_USER_CONFIG,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if (!empty($dataString)) {
                $systemConfig = $this->_translateConfig($dataString, $storeId);
            } else {
                $systemConfig = [];
            }

            $this->userConfig[$storeId] = $systemConfig;
        }

        return $this->userConfig[$storeId];
    }

    /**
     * Change the URL scheme if the current secure context would contradict frontend URL settings.
     *
     * Criteria:
     *
     *  - Given URL scheme is HTTPS
     *  - Store secure URL is configured using HTTPS; or,
     *  - Store is configured not to use secure URLs in frontend
     *
     * @param array &$data A parsed URL.
     *
     * @return void
     */
    private function _adjustFrontendUrlScheme(array &$data)
    {
        if (!empty($data['scheme']) &&
            strcasecmp($data['scheme'], 'https') === 0 &&
            (
                strcasecmp(
                    'https',
                    substr(
                        $this->scopeConfig->getValue('web/secure/base_url'),
                        0,
                        5
                    )
                ) !== 0 ||
                !$this->scopeConfig->isSetFlag('web/secure/use_in_frontend')
            )
        ) {
            $data['scheme'] = 'http';
        }
    }

    /**
     * Apply modifications to the given target path, a la Wacky Wednesday style.
     *
     * Available Modifiers
     * -------------------
     *
     *      [!] Subtracts the given term from the target path
     *      [>] Adds the given term to the target path
     *
     * Example
     * -------
     *
     *      Store Base URL:
     *      - http://www.shop.com/us/
     *
     *      Custom Frontend URL:
     *      - http://www.shop.com/!us>service
     *
     *      Result:
     *      - http://www.shop.com/service/
     *
     * Explanation
     * -----------
     *
     * Path modifiers are designed to give extra control to custom frontend URLs.
     * They are designed specifically to address Magento shops on a subfolder for
     * which the native REST API has no support.
     *
     * For example, consider this store URL:
     *  - http://www.shop.com/us/
     *
     * Unless the directory is a real server path with `api.php` in it, the URL
     * assembly and callbacks would fail. In such a case, the modifier works to
     * augment the path component of the store base URL in order to comply with
     * REST API endpoint expectations.
     *
     * Using a custom frontend URL alone is not sufficient, because the algorithm
     * for merging custom URLs with normal URLs cannot detect which part of a path
     * was for the base URL and which part was for the application route.
     *
     * Therefore, using the example above, we could would a modifier like so:
     * - http://www.shop.com/!us
     *
     * Which would assemble to a REST endpoint like the following:
     * - http://www.shop.com/api/rest/products
     *
     * @param string &$expressionPath The target path modifier expression.
     * @param string &$targetPath     The target path.
     *
     * @return void
     */
    private function _applyUrlPathModifiers(&$expressionPath, &$targetPath)
    {
        if (is_array($expressionPath)) {
            $expressionPath = &$expressionPath['path'];
        }

        if (is_array($targetPath)) {
            $targetPath = &$targetPath['path'];
        }

        preg_match_all('/([!+]*)([\w\d\/]*)/', $expressionPath, $components);

        if (!empty($components[2])) {
            foreach ($components[1] as $index => $modifier) {
                switch ($modifier) {
                    case '!':
                        $targetPath = str_replace($components[2][$index], '', $targetPath);
                        break;
                    case '>':
                        $targetPath .= $components[2][$index];
                        break;
                    default:
                        break;
                }
            }

            // Clear the expression when done so it doesn't end up in the assembled URL
            $expressionPath = '';
        }
    }

    /**
     * @param $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    //phpcs:disable
    private function _getStoreUrlHost($storeId)
    {
        $parts = parse_url(
            $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_WEB)
        );

        return (string)$parts['host'];
    }
    //phpcs:enable

    /**
     * @param      $input
     * @param null $storeId
     *
     * @return array
     */
    private function _translateConfig($input, $storeId = null)
    {
        try {
            $output = [];
            $processor = $this->template;
            $values = array_filter((preg_split('/[\r\n]+/', $input)));

            $variables = [
                'id'       => $this->getSiteId(true, $storeId),
                'store'    => $this->storeManager->getStore(),
                'category' => ($this->registry->registry('current_category') ?: $this->dataObjectFactory->create()),
                'product'  => ($this->registry->registry('current_product') ?: $this->dataObjectFactory->create()),
                'order'    => ($this->registry->registry('current_order') ?: $this->dataObjectFactory->create())
            ];

            $processor->setVariables($variables);

            foreach ($values as $row) {
                $value = explode('=', (preg_replace('/\s+=\s+/', '=', $row)));

                if (count($value) === 2) {
                    $output[$value[0]] = $processor->filter($value[1]);
                }
            }
        } catch (Exception $error) {
            $this->logger->log(LogLevel::NOTICE, $error);

            $output = [];
        }

        return $output;
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function canTrackCustomers($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_CONFIG_PATH_TRACK_CUSTOMERS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param bool $withDefault
     *
     * @return array
     */
    public function getActiveStores($withDefault = false)
    {
        $stores = [];

        foreach ($this->storeManager->getStores($withDefault) as $store) {
            if ($this->isEnabled($store->getId())) {
                $stores[$store->getId()] = $store;
            }
        }

        return $stores;
    }

    /**
     * Generate a remote callback-safe admin URL.
     *
     * @param string  $route   The target admin route.
     * @param array   $params  Optional URL parameters.
     * @param integer $storeId Store ID for context.
     *
     * @return string
     */
    public function getAdminUrl($route, array $params = [], $storeId = null)
    {
        $customUrl = parse_url(
            $this->scopeConfig->getValue(
                self::XML_CONFIG_PATH_ADMIN_URL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
        $urlData = parse_url($this->backEndUrl->getUrl($route, $params));

        if (!empty($customUrl['path'])) {
            $customUrl['path'] = rtrim($customUrl['path'], '/');

            if (empty($urlData['path'])) {
                $urlData['path'] = '';
            }

            $customUrl['path'] = $customUrl['path'] . str_replace($customUrl['path'], '', $urlData['path']);
        }

        // @see bundled functions.php for polyfill
        return http_build_url(
            array_merge(
                array_filter($urlData),
                array_filter($customUrl)
            )
        );
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getIntegrationName($storeId = null): string
    {
        return $storeId ? self::OAUTH_CONSUMER . ' #' . $storeId : self::OAUTH_CONSUMER;
    }

    /**
     * Get the remote service authorization endpoint.
     *
     * @param integer $storeId Store ID for context.
     *
     * @return string
     */
    public function getAuthUrl($storeId = null)
    {
        return $this->isTestMode($storeId) ? self::REMOTE_TEST_AUTH_URL : self::REMOTE_AUTH_URL;
    }

    /**
     * Generate a remote callback-safe frontend URL.
     *
     * @param string  $route   The target frontend route.
     * @param array   $params  Optional URL parameters.
     * @param integer $storeId Store ID for context.
     *
     * @return string
     */
    public function getFrontendUrl($route = '', array $params = [], $storeId = null)
    {
        $urlModel = $this->url->setScope($storeId);
        $urlData = parse_url($urlModel->getUrl($route, $params));
        $customUrl = parse_url(
            $this->scopeConfig->getValue(
                self::XML_CONFIG_PATH_FRONTEND_URL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );

        // REST API rewrite compatibility fix
        $urlData['path'] = ltrim(str_replace('index.php', '', (rtrim($urlData['path'], '/'))), '/');

        $this->_applyUrlPathModifiers($customUrl, $urlData);

        if (!empty($customUrl['path'])) {
            $customUrl['path'] = ltrim(str_replace('index.php', '', (rtrim($customUrl['path'], '/'))), '/');

            if (empty($urlData['path'])) {
                $urlData['path'] = '';
            }

            $customUrl['path'] = $customUrl['path'] . str_replace($customUrl['path'], '', $urlData['path']);
        }

        $urlData['path'] .= '/';

        $this->_adjustFrontendUrlScheme($urlData);

        // @see bundled functions.php for polyfill
        return http_build_url(
            array_merge(
                array_filter($urlData),
                array_filter($customUrl)
            )
        );
    }

    /**
     * @return int|mixed
     * @throws NoSuchEntityException
     */
    public function getMaxCartAgeDays()
    {
        return $this->getUserConfig()['max_cart_age_days'] ?: self::DEFAULT_MAX_CART_AGE_DAYS;
    }

    /**
     * @param bool $canGenerateFromHost
     * @param null $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSiteId($canGenerateFromHost = true, $storeId = null)
    {
        $id = (string)$this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_SITE_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($id) && $canGenerateFromHost) {
            $currentHost = $this->http->getHttpHost();
            $storeHost = $this->_getStoreUrlHost($storeId);

            if ($currentHost === $storeHost || !$storeId) {
                $id = md5($currentHost);
            } else {
                $id = md5($storeHost);
            }
        }

        return $id;
    }

    /**
     * @param null $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTrackingScriptUrl($storeId = null)
    {
        $siteId = $this->getSiteId(true, $storeId);
        $uri = $this->isTestMode() ? self::TRACKING_SCRIPT_TEST_URI : self::TRACKING_SCRIPT_URI;

        return rtrim($uri, '/') . "/{$siteId}.js";
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_CONFIG_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isTestMode($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_TEST_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function getDiagnosticTests()
    {
        return Mage::getConfig()->getNode(self::XML_CONFIG_PATH_DIAGNOSTIC_TESTS);
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    public function getProductUrlSuffix($storeId)
    {
        return $this->scopeConfig->getValue(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
