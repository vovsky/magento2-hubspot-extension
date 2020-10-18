<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterfaceFactory;

class Mode implements DiagnosticInterface
{
    public const NAME = 'mode';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DiagnosticResultInterfaceFactory
     */
    private $diagnosticResultInterfaceFactory;

    /**
     * Mode constructor.
     *
     * @param Config                                                              $config
     * @param \Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
     */
    public function __construct(Config $config, DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory)
    {
        $this->config = $config;
        $this->diagnosticResultInterfaceFactory = $diagnosticResultInterfaceFactory;
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [

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
        $result->setLabel((string)__('Integration Mode'));
        if (!$this->config->isTestMode($storeId)) {
            $result->setStatus(DiagnosticResultInterface::STATUS_PASS);
        } else {
            $result->setStatus(DiagnosticResultInterface::STATUS_WARN)
                ->setDetails('Test mode is enabled.');
        }

        return $result;
    }
}
