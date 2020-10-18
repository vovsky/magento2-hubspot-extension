<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Queue;

use Groove\Hubshoply\Api\Data\TokenInterface;
use Groove\Hubshoply\Api\Data\TokenInterfaceFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Oauth\Exception;
use Magento\Framework\Oauth\Helper\Oauth;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Oauth\Consumer;
use Magento\Integration\Model\Oauth\Token;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class Authenticate
 *
 * @package Groove\Hubshoply\Controller\Queue
 */
class Authenticate extends Action implements CsrfAwareActionInterface
{
    /**
     * @var OauthServiceInterface
     */
    private $oauthService;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Token
     */
    private $token;

    /**
     * @var TokenInterfaceFactory
     */
    private $tokenInterfaceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Oauth
     */
    private $oauthHelper;

    /**
     * Authenticate constructor.
     *
     * @param Context               $context
     * @param OauthServiceInterface $oauthService
     * @param DateTime              $dateTime
     * @param Token                 $token
     * @param TokenInterfaceFactory $tokenInterfaceFactory
     * @param LoggerInterface       $logger
     * @param Json                  $json
     * @param Oauth                 $oauthHelper
     */
    public function __construct(
        Context $context,
        OauthServiceInterface $oauthService,
        DateTime $dateTime,
        Token $token,
        TokenInterfaceFactory $tokenInterfaceFactory,
        LoggerInterface $logger,
        Json $json,
        Oauth $oauthHelper
    ) {
        parent::__construct($context);
        $this->oauthService = $oauthService;
        $this->dateTime = $dateTime;
        $this->token = $token;
        $this->tokenInterfaceFactory = $tokenInterfaceFactory;
        $this->logger = $logger;
        $this->json = $json;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws Exception
     * @throws LocalizedException
     */
    public function execute()
    {
        /**
         * @var $request Http
         */
        $request = $this->getRequest();
        $key = $request->getHeader('X-Auth-Key', false);
        $this->logger->info(
            sprintf('Request to authenticate on queue from %s', $request->getClientIp())
        );
        if ($key) {
            $consumer = $this->oauthService->loadConsumerByKey($key);
            if ($consumer->getId()) {
                try {
                    $this->validate($consumer);
                    $consumer->setUpdatedAt($this->dateTime->date());
                    $consumer->save();
                    /**
                     * @var $token TokenInterface
                     */
                    $token = $this->tokenInterfaceFactory->create();
                    $token->setToken($this->oauthHelper->generateToken());
                    $timestamp = $this->dateTime->timestamp();
                    $token->setExpires(strtotime('+1 day', (int)$timestamp));
                    $token->setConsumerId($consumer->getId());
                    $token->save();
                    /**
                     * @var $response \Magento\Framework\App\Response\Http
                     */
                    $response = $this->getResponse();
                    $responseBody = [
                        'token'   => $token->getToken(),
                        'expires' => (int)$token->getExpires(),
                    ];
                    $response->setHeader('X-Access-Token', $token->getToken())
                        ->setHeader('X-Access-Expires', $token->getExpires())
                        ->representJson($this->json->serialize($responseBody))
                        ->sendResponse();
                } catch (Exception $e) {
                    $this->sendError(401, 'OAuth Exception', $e->getMessage());
                } catch (\Exception $e) {
                    $this->sendError(
                        500,
                        'Server Error',
                        'Exception thrown while authenticating this request.'
                    );
                }
            } else {
                $this->logger->log(
                    LogLevel::ERROR,
                    'Consumer is not found'
                );
                $this->sendError(400, 'OAuth Error', 'Consumer is not found');
            }
        } else {
            $this->logger->log(
                LogLevel::ERROR,
                sprintf('Header is not set')
            );
            $this->sendError(400, 'OAuth Error', 'Header is not set');
        }
    }

    /**
     * @param Consumer $consumer
     *
     * @throws LocalizedException
     */
    private function validate(Consumer $consumer)
    {
        $secret = $this->getRequest()->getHeader('X-Auth-Secret', false);

        if (strcmp($secret, $consumer->getSecret()) !== 0) {
            throw new LocalizedException(__('OAuth secret key rejected.'));
        }
    }

    /**
     * @param null   $code
     * @param string $message
     * @param string $details
     * @param null   $callback
     */
    private function sendError($code = null, $message = '', $details = '', $callback = null)
    {
        if ((int)$code < 100) {
            $code = 500;
        }

        $data = [
            'error_code'    => $code,
            'error_message' => $message,
            'error_details' => $details,
        ];
        /**
         * @var $response \Magento\Framework\App\Response\Http
         */
        $response = $this->getResponse();
        $response->setStatusCode($code);
        $response->representJson($this->json->serialize($data));
        $response->sendResponse();
    }

    /**
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
