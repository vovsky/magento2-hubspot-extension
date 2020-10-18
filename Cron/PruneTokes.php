<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Cron;

use Groove\Hubshoply\Model\Config;
use Groove\Hubshoply\Model\ResourceModel\Token\Collection;
use Groove\Hubshoply\Model\ResourceModel\Token\CollectionFactory;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

/**
 * Class PruneTokes
 *
 * @package Groove\Hubshoply\Cron
 */
class PruneTokes
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * PruneTokes constructor.
     *
     * @param Config            $config
     * @param LoggerInterface   $logger
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Config $config, LoggerInterface $logger, CollectionFactory $collectionFactory)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     *
     */
    public function execute()
    {
        if ($this->config->getActiveStores()) {
            //get all tokens with an additional column
            // telling how many days they have until they expire
            /**
             * @var $tokens Collection
             */
            $tokens = $this->collectionFactory->create();

            $tokens->getSelect()
                ->columns(['time_diff_token_expirey' => new Zend_Db_Expr('TIMESTAMPDIFF(MINUTE, NOW(), expires)')])
                ->having('time_diff_token_expirey <= 0');

            $total = count($tokens);

            $tokens->walk('delete');

            $this->logger->info(sprintf('Pruned %d expired tokens.', $total));
        }
    }
}
