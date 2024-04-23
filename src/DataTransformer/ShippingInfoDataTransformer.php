<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ShipmentInterface;

final class ShippingInfoDataTransformer implements ShippingInfoDataTransformerInterface
{
    /**
     * @return array<string, string|array>
     */
    public function transform(PaymentInterface $payment): array
    {
        $data = [];
        $order = $payment->getOrder();

        if ($order === null || !$order->hasShipments()) {
            return $data;
        }

        /** @var ShipmentInterface $shipment */
        $shipment = $order->getShipments()->first();
        $shippingMethod = $shipment->getMethod();
        if ($shippingMethod === null) {
            return $data;
        }

        $shippingInfo = [];

        $shippingInfo['shipping_company'] = $shippingMethod->getName();
        if ($shipment->getState() === ShipmentInterface::STATE_SHIPPED && $shipment->isTracked()) {
            $shippingInfo['tracking_number'] = $shipment->getTracking();
        }

        $data['shipping_info'] = [$shippingInfo];

        return $data;
    }
}
