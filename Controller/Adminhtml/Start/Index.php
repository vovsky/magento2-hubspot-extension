<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Adminhtml\Start;

use Exception;
use Groove\Hubshoply\Model\Setup;
use Groove\Hubshoply\Model\Setup\SetupException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Groove_Hubshoply::start_setup';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Setup
     */
    private $setup;

    public function __construct(
        Context $context,
        Session $session,
        Setup $setup
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->setup = $setup;
    }

    public function execute()
    {
        try {
            $storeId = $this->getRequest()->getParam('store');
            $userId = $this->session->getUser() === null ? 0 : $this->session->getUser()->getId();
            $this->setup->autoInstall($userId, $storeId);
        } catch (SetupException $setupException) {
            $this->messageManager->addErrorMessage($setupException->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $result = $this->resultRedirectFactory->create();
        $result->setPath(
            'adminhtml/system_config/edit',
            [
                'section' => 'hubshoply',
                'website' => $this->getRequest()->getParam('website'),
                'store'   => $this->getRequest()->getParam('store'),
            ]
        );

        return $result;
    }
}
