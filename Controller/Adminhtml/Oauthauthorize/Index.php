<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Adminhtml\Oauthauthorize;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;

class Index extends Action
{
    private const CALLBACK_PARAM = 'oauth_callback';

    private const TOKEN_PARAM = 'oauth_token';


    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    public function __construct(
        Context $context,
        TokenFactory $tokenFactory,
        IntegrationServiceInterface $integrationService
    ) {
        parent::__construct($context);
        $this->tokenFactory = $tokenFactory;
        $this->integrationService = $integrationService;
    }

    public function execute()
    {
        $result = $this->resultRedirectFactory->create();
        $url = $this->_request->getParam(self::CALLBACK_PARAM);
        $token = $this->_request->getParam(self::TOKEN_PARAM);
        $token = $this->tokenFactory->create()->loadByToken($token);

        if ($token->getId() && $token->getType() == Token::TYPE_REQUEST && $url) {
            $integration = $this->integrationService->findByConsumerId($token->getConsumerId());
            $integration->setStatus(IntegrationModel::STATUS_ACTIVE);
            $integration->save();
            $result->setUrl($url . '?' . http_build_query([
                    'oauth_token'    => $token->getToken(),
                    'oauth_verifier' => $token->getVerifier(),
                ]));

            return $result;
        }

        return $this->resultFactory->create(ResultFactory::TYPE_RAW)->setHttpResponseCode(400);
    }
}
