<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Adminhtml\Diagnostic;

use Exception;
use Groove\Hubshoply\Model\Diagnostic;
use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use Magento\Framework\Phrase;

/**
 * Class Index
 *
 * @package Groove\Hubshoply\Controller\Adminhtml\Diagnostic
 */
class Index extends Action
{

    /**
     * @var Diagnostic
     */
    private $diagnostic;

    /**
     * @var NotifierPool
     */
    private $notifier;

    /**
     * Index constructor.
     *
     * @param Context      $context
     * @param Diagnostic   $diagnostic
     * @param NotifierPool $notifier
     */
    public function __construct(
        Context $context,
        Diagnostic $diagnostic,
        NotifierPool $notifier
    ) {
        parent::__construct($context);
        $this->diagnostic = $diagnostic;
        $this->notifier = $notifier;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            /**
             * @var $results DiagnosticResultInterface[]
             */
            $results = $this->diagnostic->run([], $this->getRequest()->getParam('store'));
            $this->processResults($results);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__("There is an error during running the test"));
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $params = [
            'section' => 'hubshoply',
            'store'   => $this->getRequest()->getParam('store'),
        ];
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$results) {
            $resultRedirect->setPath('adminhtml/system_config/edit', $params);
        } else {
            $resultRedirect->setPath('adminhtml/notification/index');
        }

        return $resultRedirect;
    }

    /**
     * @param DiagnosticResultInterface[] $results
     */
    private function processResults(array $results)
    {
        foreach ($results as $diagnosticResult) {
            switch ($diagnosticResult->getStatus()) {
                case DiagnosticResultInterface::STATUS_PASS:
                    $this->notifier->addNotice(
                        $diagnosticResult->getLabel(),
                        $diagnosticResult->getDetails() ?:
                            $this->getDefaultDetails(DiagnosticResultInterface::STATUS_PASS),
                        $diagnosticResult->getUrl()
                    );
                    break;
                case DiagnosticResultInterface::STATUS_WARN:
                    $this->notifier->addMajor(
                        $diagnosticResult->getLabel(),
                        $diagnosticResult->getDetails() ?:
                            $this->getDefaultDetails(DiagnosticResultInterface::STATUS_WARN),
                        $diagnosticResult->getUrl()
                    );
                    break;
                case DiagnosticResultInterface::STATUS_FAIL:
                    $this->notifier->addCritical(
                        $diagnosticResult->getLabel(),
                        $diagnosticResult->getDetails() ?:
                            $this->getDefaultDetails(DiagnosticResultInterface::STATUS_FAIL),
                        $diagnosticResult->getUrl()
                    );
                    break;
                case DiagnosticResultInterface::STATUS_SKIP:
                    $this->notifier->addMinor(
                        $diagnosticResult->getLabel(),
                        $diagnosticResult->getDetails() ?:
                            $this->getDefaultDetails(DiagnosticResultInterface::STATUS_SKIP),
                        $diagnosticResult->getUrl()
                    );
                    break;
            }
        }
    }

    /**
     * @param $code
     *
     * @return Phrase
     */
    private function getDefaultDetails($code): Phrase
    {
        $details = [
            DiagnosticResultInterface::STATUS_PASS => __("Pass"),
            DiagnosticResultInterface::STATUS_WARN => __("Warning"),
            DiagnosticResultInterface::STATUS_FAIL => __("Fail"),
            DiagnosticResultInterface::STATUS_SKIP => __("Skipped")
        ];

        return $details[$code] ?: __("Unknown");
    }
}

