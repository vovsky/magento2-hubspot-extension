<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterfaceFactory;

class CustomerTracking implements DiagnosticInterface
{
    public const NAME           = 'customer_tracking';
    public const KB_ARTICLE_URL = 'http://support.hubshop.ly/magento/magento-customer-tracking';

    /**
     * @var DiagnosticResultInterfaceFactory
     */
    private $diagnosticResultInterfaceFactory;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config, DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory)
    {
        $this->diagnosticResultInterfaceFactory = $diagnosticResultInterfaceFactory;
        $this->config = $config;
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
        /**
         * @var $result DiagnosticResultInterface
         */
        $result = $this->diagnosticResultInterfaceFactory->create();
        $result->setLabel((string)__('Customer Tracking'));
        if ($this->config->canTrackCustomers($storeId)) {
            $result->setStatus(DiagnosticResultInterface::STATUS_PASS);
        } else {
            $result->setStatus(DiagnosticResultInterface::STATUS_WARN);
            $result->setDetails('Customer tracking is not enabled.');
            $result->setUrl(self::KB_ARTICLE_URL);
        }

        return $result;
    }
}
