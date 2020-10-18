<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Adminhtml\State;

use Exception;
use Groove\Hubshoply\Model\Setup;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Reset extends Action
{
    /**
     * @var Setup
     */
    private $setup;

    public function __construct(
        Context $context,
        Setup $setup
    ) {
        parent::__construct($context);
        $this->setup = $setup;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $this->setup->reset($this->getRequest()->getParam('store'));
            $this->messageManager->addSuccessMessage(__("The state has been reseted"));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__("The state has not been reseted"));
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $params = [
            'section' => 'hubshoply',
            'store'   => $this->getRequest()->getParam('store'),
        ];
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('adminhtml/system_config/edit', $params);

        return $resultRedirect;
    }
}
