<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Plugin;

use Groove\Hubshoply\Model\Config;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CustomerData
 *
 * @package Groove\Hubshoply
 */
class CustomerData
{
    const EMAIL_INDEX = 'customerEmail';
    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CustomerData constructor.
     *
     * @param CurrentCustomer       $currentCustomer
     * @param Config                $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(CurrentCustomer $currentCustomer, Config $config, StoreManagerInterface $storeManager)
    {
        $this->currentCustomer = $currentCustomer;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param                                         $result
     *
     * @return mixed
     */
    public function afterGetSectionData(
        \Magento\Customer\CustomerData\Customer $subject,
        $result
    ) {
        if (!$result) {
            return $result;
        }
        try {
            $storeId = $this->storeManager->getStore()->getId();
            if ($this->config->isEnabled($storeId) && $this->config->canTrackCustomers($storeId)) {
                $customer = $this->currentCustomer->getCustomer();
                $result[self::EMAIL_INDEX] = $customer->getEmail();
            }
        } catch (NoSuchEntityException $e) {
            return $result;
        }

        return $result;
    }
}
