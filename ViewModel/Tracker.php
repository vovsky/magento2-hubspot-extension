<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\ViewModel;

use Groove\Hubshoply\Model\Config;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Tracker
 *
 * @package Groove\Hubshoply\ViewModel
 */
class Tracker implements ArgumentInterface
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * Tracker constructor.
     *
     * @param Json                  $json
     * @param Config                $config
     * @param StoreManagerInterface $storeManager
     * @param HttpContext           $context
     */
    public function __construct(
        Json $json,
        Config $config,
        StoreManagerInterface $storeManager,
        HttpContext $context
    ) {
        $this->json = $json;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->httpContext = $context;
    }

    /**
     * @return string
     */
    public function getScriptUrl(): string
    {
        try {
            return $this->config->getTrackingScriptUrl($this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getConfigJson(): string
    {
        try {
            $config = $this->config->getUserConfig($this->storeManager->getStore()->getId());

            return $this->json->serialize($config);
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        try {
            return $this->config->isEnabled($this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @return mixed|null
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function canTrackCustomer(): bool
    {
        try {
            return $this->config->canTrackCustomers($this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
