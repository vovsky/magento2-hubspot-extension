<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterfaceFactory;
use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Integration\Api\IntegrationServiceInterface;

class Role implements DiagnosticInterface
{
    public const NAME           = 'role';
    public const KB_ARTICLE_URL = 'http://support.hubshop.ly/magento/magento-rest-roles';

    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var AclRetriever
     */
    private $aclRetriever;

    /**
     * @var RootResource
     */
    private $rootResource;

    /**
     * @var DiagnosticResultInterfaceFactory
     */
    private $diagnosticResultInterfaceFactory;

    public function __construct(
        RoleFactory $roleFactory,
        Config $config,
        IntegrationServiceInterface $integrationService,
        AclRetriever $aclRetriever,
        RootResource $rootResource,
        DiagnosticResultInterfaceFactory $diagnosticResultInterfaceFactory
    ) {
        $this->roleFactory = $roleFactory;
        $this->config = $config;
        $this->integrationService = $integrationService;
        $this->aclRetriever = $aclRetriever;
        $this->rootResource = $rootResource;
        $this->diagnosticResultInterfaceFactory = $diagnosticResultInterfaceFactory;
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
        $result->setLabel((string)__('REST Role permission'));
        $integrationName = $this->config->getIntegrationName($storeId);
        $integration = $this->integrationService->findByName($integrationName);
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . $integration->getId();
        $role = $this->roleFactory->create()->load($roleName, 'role_name');
        $acls = $this->aclRetriever->getAllowedResourcesByRole($role->getId());

        if (in_array($this->rootResource->getId(), $acls)) {
            $result->setStatus(DiagnosticResultInterface::STATUS_PASS);
        } else {
            $result->setStatus(DiagnosticResultInterface::STATUS_FAIL)
                ->setDetails('Integration does not have all permissions')
                ->setUrl(self::KB_ARTICLE_URL);
        }

        return $result;
    }
}
