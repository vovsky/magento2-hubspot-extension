<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Block\Adminhtml\Frontend\Setup;

use Groove\Hubshoply\Helper\Oauth;
use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\Diagnostic;
use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Oauth\Exception;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManager;

class Connector extends Template implements RendererInterface
{
    /**
     * @var Diagnostic
     */
    private $diagnostic;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Oauth
     */
    private $oauthHelper;

    public function __construct(
        Context $context,
        Diagnostic $diagnostic,
        Config $config,
        UrlInterface $url,
        Oauth $oauthHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->diagnostic = $diagnostic;
        $this->storeManager = $context->getStoreManager();
        $this->config = $config;
        $this->url = $url;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * Local constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('hubshoply/connector.phtml');
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore($this->_request->getParam('store'))->getId();
    }

    /**
     * @return $this|Connector
     * @throws NoSuchEntityException
     * @throws IntegrationException
     * @throws LocalizedException
     * @throws Exception
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->needsProvisioning()) {
            $this->setTemplate('hubshoply/provisioning-notice.phtml');
        } else {
            $this->setFrameSrc(
                preg_replace(
                    '~https?://~',
                    '//',
                    $this->oauthHelper->buildUrl($this->config->getAuthUrl($this->getStoreId()), null, true)
                )
            );
        }

        return $this;
    }

    /**
     * Generate the URL to start store setup.
     *
     * @return string
     */
    public function getStartUrl()
    {
        return $this->url->getUrl('hubshoply/start', ['_current' => true]);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function needsProvisioning()
    {
        $tests = ['consumer'];
        $results = $this->diagnostic->setSkipDependencyCheckFlag(true)
            ->run($tests, (string)$this->getStoreId());

        foreach ($results as $result) {
            if ($result->getStatus() !== DiagnosticResultInterface::STATUS_PASS) {
                return true;
            }
        }

        return false;
    }

    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }
}
