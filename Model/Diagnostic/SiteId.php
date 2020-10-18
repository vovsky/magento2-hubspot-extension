<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterfaceFactory;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Class SiteId
 *
 * @package Groove\Hubshoply\Model\Diagnostic
 */
class SiteId implements DiagnosticInterface
{

    public const NAME = 'site_id';

    public const KB_ARTICLE_URL = 'http://support.hubshop.ly/magento/magento-tracking-script';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DiagnosticResultInterfaceFactory
     */
    private $diagnosticResultInterfaceFactory;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * SiteId constructor.
     *
     * @param Config                                                              $config
     * @param DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
     * @param Curl                                                                $curl
     */
    public function __construct(
        Config $config,
        DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory,
        Curl $curl
    ) {
        $this->config = $config;
        $this->diagnosticResultInterfaceFactory = $diagnosticResultInterfaceFactory;
        $this->curl = $curl;
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            Enabled::NAME => DiagnosticResultInterface::STATUS_PASS,
        ];
    }

    /**
     * @inheritDoc
     */
    public function run($storeId): DiagnosticResultInterface
    {
        $url = $this->config->getTrackingScriptUrl($storeId);

        /**
         * @var $result DiagnosticResultInterface
         */
        $result = $this->diagnosticResultInterfaceFactory->create();
        $result->setLabel((string)__('Tracking Script'));
        if (!$this->_validateTrackingScript($url)) {
            $result->setStatus(DiagnosticResultInterface::STATUS_FAIL)
                ->setDetails('Tracking script failed to load for the current site ID.')
                ->setUrl(self::KB_ARTICLE_URL);
        } else {
            $result->setStatus(DiagnosticResultInterface::STATUS_PASS);
        }

        return $result;
    }

    /**
     * Confirm the tracking script is working.
     *
     * @param string $url The URL to the tracking script.
     *
     * @return boolean
     */
    private function _validateTrackingScript($url)
    {
        $url = preg_replace('~^//~', 'https://', $url);

        $this->curl->setOption(CURLOPT_NOBODY, true);

        $this->curl->get($url);

        return $this->curl->getStatus() === 200;
    }
}