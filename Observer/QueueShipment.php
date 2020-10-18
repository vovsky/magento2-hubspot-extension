<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Observer;

use DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Monolog\Logger;

class QueueShipment extends BaseObserver implements ObserverInterface
{

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();
        if (!$this->isEnabled($shipment->getStoreId())) {
            return;
        }

        $email = $shipment->getOrder()->getCustomerEmail();
        $tracking = $shipment->getAllTracks();
        $tracks = [];
        $carriers = [];

        foreach ($tracking as $track) {
            /**
             * @var $track Track
             */
            $tracks[] = $track->getNumber();
            $carriers[] = $track->getTitle();
        }

        $this->queueItemManagement->create(
            'shipment',
            'created',
            [
                'email'              => $email,
                'created_at'         => date(DateTime::W3C, strtotime($shipment->getCreatedAt())),
                'tracking_number'    => $tracks,
                'tracking_carrier'   => $carriers,
                'product_url_suffix' => $this->config->getProductUrlSuffix($shipment->getStoreId()),
            ],
            (string)$shipment->getStoreId()
        );

        $this->logger->log(Logger::DEBUG, sprintf('Event queued: shipment.created(%d).', $shipment->getId()));
    }
}
