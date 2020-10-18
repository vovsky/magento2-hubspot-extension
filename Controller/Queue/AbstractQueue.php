<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Queue;

use Groove\Hubshoply\Api\Data\TokenInterface;
use Groove\Hubshoply\Api\Data\TokenInterfaceFactory;
use Groove\Hubshoply\Helper\Error;
use Groove\Hubshoply\Model\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractQueue
 *
 * @package Groove\Hubshoply\Controller\Queue
 */
abstract class AbstractQueue extends Action implements HttpGetActionInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TokenInterfaceFactory
     */
    private $tokenInterfaceFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var Error
     */
    protected $error;

    /**
     * AbstractQueue constructor.
     *
     * @param Context               $context
     * @param LoggerInterface       $logger
     * @param TokenInterfaceFactory $tokenInterfaceFactory
     * @param Config                $config
     * @param StoreManager          $storeManager
     * @param Error                 $error
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TokenInterfaceFactory $tokenInterfaceFactory,
        Config $config,
        StoreManager $storeManager,
        Error $error
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->tokenInterfaceFactory = $tokenInterfaceFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->error = $error;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->logRequest();
        $response = $this->checkAuthorization();
        if ($response instanceof Http) {
            $response->sendResponse();

            return;
        }

        return $this->get();
    }

    /**
     * @return ResponseInterface
     */
    abstract protected function get(): ResponseInterface;

    /**
     * @return mixed
     */
    abstract protected function logRequest();

    /**
     * @return Http|null
     */
    private function checkAuthorization(): ?Http
    {
        $accessToken = $this->getRequest()->getHeader('X-Access-Token');
        /**
         * @var $tokenModel TokenInterface
         */
        $tokenModel = $this->tokenInterfaceFactory->create()->load($accessToken, 'token');

        if (!$tokenModel->getId() || $tokenModel->isExpired()) {
            return $this->error->prepareResponse(
                401,
                'Unauthorized',
                'Your token is invalid or not present. Please re-authenticate and try again.',
                function ($response) {
                    $response->setHeader('WWW-Authenticate', 'Custom', true);
                }
            );
        } else {
            if (!$this->isStoreEnabled()) {
                return $this->error->prepareResponse(
                    503,
                    'Service Unavailable',
                    'The HubShop.ly service is not currently enabled for this shop.'
                );
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    private function isStoreEnabled(): bool
    {
        try {
            return $this->config->isEnabled($this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
