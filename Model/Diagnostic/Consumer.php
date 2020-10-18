<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterfaceFactory;
use Magento\Integration\Api\IntegrationServiceInterface;

class Consumer implements DiagnosticInterface
{
    public const NAME = 'consumer';

    const KB_ARTICLE_URL = 'http://support.hubshop.ly/magento/magento-oauth-consumer';

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DiagnosticResultInterfaceFactory
     */
    private $diagnosticResultInterfaceFactory;

    /**
     * Consumer constructor.
     *
     * @param IntegrationServiceInterface                                         $integrationService
     * @param Config                                                              $config
     * @param DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        Config $config,
        DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
    ) {
        $this->integrationService = $integrationService;
        $this->config = $config;
        $this->diagnosticResultInterfaceFactory = $diagnosticResultInterfaceFactory;
    }

    public function getDependencies(): array
    {
        return [
            Enabled::NAME => DiagnosticResultInterface::STATUS_PASS,
        ];
    }

    public function run($storeId): DiagnosticResultInterface
    {
        $integrationName = $this->config->getIntegrationName($storeId);
        $integration = $this->integrationService->findByName($integrationName);
        /**
         * @var $result DiagnosticResultInterface
         */
        $result = $this->diagnosticResultInterfaceFactory->create();
        $result->setLabel((string)__('OAuth Consumer'));
        if ($integration->getId()) {
            $result->setStatus(DiagnosticResultInterface::STATUS_PASS);
        } else {
            $result->setStatus(DiagnosticResultInterface::STATUS_FAIL);
            $result->setDetails((string)__('OAuth Consumer is not available.'));
            $result->setUrl(self::KB_ARTICLE_URL);
        }

        return $result;
    }
}
