<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Observer\Customer;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\QueueItemManagement;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class CustomerBaseObserver extends \Groove\Hubshoply\Observer\BaseObserver
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * CustomerBaseObserver constructor.
     *
     * @param Config                   $config
     * @param QueueItemManagement      $queueItemManagement
     * @param LoggerInterface          $logger
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        Config $config,
        QueueItemManagement $queueItemManagement,
        LoggerInterface $logger,
        GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($config, $queueItemManagement, $logger);
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param $customer
     *
     * @return string
     */
    protected function getCustomerGroupCode($customer)
    {
        try {
            $group = $this->groupRepository->getById($customer->getGroupId());
        } catch (NoSuchEntityException $e) {
            return '';
        } catch (LocalizedException $e) {
            return '';
        }

        return $group->getCode();
    }
}
