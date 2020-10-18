<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Cron;

use Exception;
use Groove\Hubshoply\Model\AbandonedCart;
use Groove\Hubshoply\Model\AbandonedCartFactory;
use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\ResourceModel\AbandonedCart\Collection;
use Groove\Hubshoply\Model\ResourceModel\AbandonedCart\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AbandonCartScan
 *
 * @package Groove\Hubshoply\Cron
 */
class AbandonCartScan
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var int
     */
    private $minutesUntilAbandoned;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var CollectionFactory
     */
    private $abandonedCartCollectionFactory;

    /**
     * @var AbandonedCartFactory
     */
    private $abandonedCartFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AbandonCartScan constructor.
     *
     * @param Config                                                       $config
     * @param \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
     * @param QuoteRepository                                              $quoteRepository
     * @param SearchCriteriaBuilder                                        $searchCriteriaBuilder
     * @param FilterBuilder                                                $filterBuilder
     * @param FilterGroupBuilder                                           $filterGroupBuilder
     * @param StoreRepositoryInterface                                     $storeRepository
     * @param CollectionFactory                                            $abandonedCartCollectionFactory
     * @param AbandonedCartFactory                                         $abandonedCartFactory
     * @param LoggerInterface                                              $logger
     * @param int                                                          $minutesUntilAbandoned
     */
    public function __construct(
        Config $config,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $collectionFactory,
        QuoteRepository $quoteRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        StoreRepositoryInterface $storeRepository,
        CollectionFactory $abandonedCartCollectionFactory,
        AbandonedCartFactory $abandonedCartFactory,
        LoggerInterface $logger,
        $minutesUntilAbandoned = 60
    ) {
        $this->config = $config;
        $this->minutesUntilAbandoned = $minutesUntilAbandoned;
        $this->collectionFactory = $collectionFactory;
        $this->storeRepository = $storeRepository;
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->abandonedCartCollectionFactory = $abandonedCartCollectionFactory;
        $this->abandonedCartFactory = $abandonedCartFactory;
        $this->logger = $logger;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $stores = $this->storeRepository->getList();
        $filterGroups = [];
        $adapter = $this->collectionFactory->create()->getConnection();
        $quoteIds = [];
        function now($dayOnly = false)
        {
            return date($dayOnly ? 'Y-m-d' : 'Y-m-d H:i:s');
        }

        foreach ($stores as $store) {
            if ($this->config->isEnabled($store->getId())) {
                $filters = [];
                $userConfig = $this->config->getUserConfig($store->getId());
                if ($userConfig['minutes_until_abandoned']) {
                    $abandonAge = (int)$userConfig['minutes_until_abandoned'];
                } else {
                    $abandonAge = $this->minutesUntilAbandoned;
                }
                $upperBound = $adapter->getDateSubSql($adapter->quote(now()), $abandonAge, $adapter::INTERVAL_MINUTE);
                $lowerBound = $adapter->getDateSubSql(
                    $adapter->quote(now()),
                    $userConfig['max_cart_age_days'],
                    $adapter::INTERVAL_DAY
                );
                $filters[] = $this->filterBuilder->setConditionType('eq')
                    ->setField('converted_at')
                    ->setValue('0000-00-00 00:00:00')->create();
                $filters[] = $this->filterBuilder->create()
                    ->setConditionType('from')
                    ->setField('updated_at')
                    ->setValue($lowerBound);
                $filters[] = $this->filterBuilder
                    ->setConditionType('to')
                    ->setField('updated_at')
                    ->setValue($upperBound)->create();
                $filters[] = $this->filterBuilder
                    ->setConditionType('notnull')
                    ->setField('customer_email')->create();
                $filters[] = $this->filterBuilder
                    ->setConditionType('eg')
                    ->setField('is_active')
                    ->setValue(1)->create();
                $filters[] = $this->filterBuilder
                    ->setConditionType('eg')
                    ->setField('store_id')
                    ->setValue($store->getId())->create();
                $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)->create();
                foreach ($this->quoteRepository->getList($searchCriteria)->getItems() as $quote) {
                    $this->trackAbandonCart($quote);
                    $quoteIds[] = $quote->getId();
                }
            }
        }

        // @todo consider process deferment or other refactor
        /**
         * @var $abandonedCartCollection Collection
         */
        $abandonedCartCollection = $this->abandonedCartCollectionFactory->create();
        $abandonedCartCollection->addFieldToFilter('quote_id', ['nin' => $quoteIds])
            ->walk('delete');

        $this->logger->info(sprintf('Queued %d abandoned carts.', count($quoteIds)));
    }

    /**
     * @param CartInterface $quote
     */
    private function trackAbandonCart(CartInterface $quote)
    {
        /**
         * @var $cart AbandonedCart
         */
        $cart = $this->abandonedCartFactory->create();
        $cart->loadByQuoteStore($quote->getId(), $quote->getStoreId());

        if (!$cart->getId()) {
            $cart->setCreatedAt($quote->getCreatedAt())
                ->setQuoteId($quote->getId())
                ->setStoreId($quote->getStoreId());
        }

        if ($cart->getUpdatedAt() != $quote->getUpdatedAt()) {
            $cart->setUpdatedAt($quote->getUpdatedAt())
                ->setEnqueued(false);
        }

        try {
            $cart->save();
        } catch (Exception $error) {
            $this->logger->error($error->getMessage());
        }
    }
}
