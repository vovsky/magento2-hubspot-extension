<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterfaceFactory;
use Groove\Hubshoply\Model\ResourceModel\Log\Collection;
use Groove\Hubshoply\Model\ResourceModel\Log\CollectionFactory;
use Monolog\Logger;

/**
 * Class Log
 *
 * @package Groove\Hubshoply\Model\Diagnostic
 */
class Log implements DiagnosticInterface
{
    /**
     *
     */
    public const NAME = 'log';
    /**
     *
     */
    public const KB_ARTICLE_URL = 'http://support.hubshop.ly/magento/magento-system-log';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DiagnosticResultInterfaceFactory
     */
    private $diagnosticResultInterfaceFactory;

    /**
     * Log constructor.
     *
     * @param CollectionFactory                $collectionFactory
     * @param DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
    ) {
        $this->collectionFactory = $collectionFactory;
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
        /**
         * @var $result DiagnosticResultInterface
         */
        $result = $this->diagnosticResultInterfaceFactory->create();
        $result->setLabel((string)__('System Log'));
        /**
         * @var $logCollection Collection
         */
        $logCollection = $this->collectionFactory->create();
        $logCollection->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter(
                'level',
                [
                    'in' => [
                        Logger::WARNING,
                        Logger::ERROR,
                        Logger::CRITICAL,
                        Logger::ALERT,
                        Logger::EMERGENCY
                    ]
                ]
            );
        if ($logCollection->getSize() == 0) {
            $result->setStatus(DiagnosticResultInterface::STATUS_PASS);
        } else {
            $result->setStatus(DiagnosticResultInterface::STATUS_WARN);
            $result->setDetails('Errors found in HubShop.ly system log.');
            $result->setUrl(self::KB_ARTICLE_URL);
        }

        return $result;
    }
}
