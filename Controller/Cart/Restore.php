<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Controller\Cart;

use Exception;
use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Restore
 *
 * @package Groove\Hubshoply\Controller\Cart
 */
class Restore extends Cart
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Restore constructor.
     *
     * @param Context              $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session                    $checkoutSession
     * @param StoreManagerInterface         $storeManager
     * @param Validator     $formKeyValidator
     * @param CustomerCart                                       $cart
     * @param CartRepositoryInterface                            $cartRepository
     * @param LoggerInterface                                    $logger
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerCart $cart,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /**
         * @var $store Store
         */
        $store = $this->_storeManager->getStore();
        $path = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $activeQuote = $this->_checkoutSession->getQuote();
        $targetQuoteId = (int)($this->getRequest()->getParam('quote'));

        if (is_int($targetQuoteId) && $targetQuoteId > 0) {
            if ($activeQuote->getId() == $targetQuoteId) {
                $path = $store->getUrl('checkout/cart');
            } else {
                $targetQuote = $this->cartRepository->get($targetQuoteId);

                if ($targetQuote->getId() > 0) {
                    try {
                        $activeQuote->merge($targetQuote)->collectTotals();
                        $this->cartRepository->save($targetQuote);
                    } catch (Exception $error) {
                        $this->logger->error($error);

                        $this->messageManager->addErrorMessage(
                            __('There was a problem restoring your cart.')
                        );
                    }

                    $path = $store->getUrl('checkout/cart');
                }
            }
        }

        return $this->_redirect($path);
    }
}
