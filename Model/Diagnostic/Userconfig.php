<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterfaceFactory;

class Userconfig implements DiagnosticInterface
{
    public const NAME           = 'user_config';
    public const KB_ARTICLE_URL = 'http://support.hubshop.ly/magento/magento-customer-tracking';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DiagnosticResultInterfaceFactory
     */
    private $diagnosticResultInterfaceFactory;

    public function __construct(
        Config $config,
        DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
    ) {
        $this->config = $config;
        $this->diagnosticResultInterfaceFactory = $diagnosticResultInterfaceFactory;
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function run($storeId): DiagnosticResultInterface
    {
        $values = $this->config->getUserConfig($storeId);
        /**
         * @var $result DiagnosticResultInterface
         */
        $result = $this->diagnosticResultInterfaceFactory->create();
        $result->setLabel((string)__('User Configuration'));
        $result->setStatus(DiagnosticResultInterface::STATUS_PASS);
        if (empty($values)) {
            $result->setStatus(DiagnosticResultInterface::STATUS_WARN)
                ->setDetails('Failed to parse user configuration on')
                ->setUrl(self::KB_ARTICLE_URL);
        }

        return $result;
    }
}
