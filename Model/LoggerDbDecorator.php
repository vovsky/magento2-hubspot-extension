<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model;

use Exception;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class Logger
 *
 * @package Groove\Hubshoply\Model
 */
class LoggerDbDecorator implements LoggerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LogFactory
     */
    private $factory;

    /**
     * @var ResourceModel\Log
     */
    private $logResource;

    /**
     * Logger constructor.
     *
     * @param Logger   $logger
     * @param LogFactory        $factory
     * @param ResourceModel\Log $logResource
     */
    public function __construct(
        Logger $logger,
        LogFactory $factory,
        ResourceModel\Log $logResource
    ) {
        $this->logger = $logger;
        $this->factory = $factory;
        $this->logResource = $logResource;
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = [], $saveToDb = true)
    {
        if ($saveToDb === true) {
            $this->saveToDb($this->logger::EMERGENCY, $message);
        }
        $this->logger->emergency($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = [], $saveToDb = true)
    {
        if ($saveToDb === true) {
            $this->saveToDb($this->logger::ALERT, $message);
        }

        $this->logger->alert($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = [], $saveToDb = true)
    {
        if ($saveToDb === true) {
            $this->saveToDb($this->logger::CRITICAL, $message);
        }
        $this->logger->critical($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = [], $saveToDb = true)
    {
        if ($saveToDb === true) {
            $this->saveToDb($this->logger::ERROR, $message);
        }
        $this->logger->error($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = [], $saveToDb = true)
    {
        if ($saveToDb === true) {
            $this->saveToDb($this->logger::WARNING, $message);
        }
        $this->logger->warning($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = [], $saveToDb = true)
    {
        if ($saveToDb === true) {
            $this->saveToDb($this->logger::NOTICE, $message);
        }
        $this->logger->notice($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = [], $saveToDb = true)
    {
        if ($saveToDb === true) {
            $this->saveToDb($this->logger::INFO, $message);
        }
        $this->logger->info($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = [], $saveToDb = true)
    {
        if ($saveToDb === true) {
            $this->saveToDb($this->logger::DEBUG, $message);
        }
        $this->logger->debug($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [], $saveToDb = true)
    {
        if ($saveToDb === true) {
            $this->saveToDb($level, $message);
        }
        $this->logger->log($level, $message, $context);
    }

    /**
     * @param $level
     * @param $message
     *
     */
    private function saveToDb($level, $message)
    {
        try {
            /**
             * @var $logModel Log
             */
            $logModel = $this->factory->create();
            $logModel->setLevel($level);
            $logModel->setMessage($message);
            $this->logResource->save($logModel);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
