<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class ShipmentDataTransformer implements DataTransformerInterface
{
    public function __invoke(array $data, PaymentInterface $payment): array
    {
        Assert::notNull($payment->getOrder());

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        if ($order->getShipments()->isEmpty()) {
            return $data;
        }

        if (is_array($data['order_lines'])) {
            $data['order_lines'][] = [
                'quantity' => 1,
                'name' => 'Shipping Fee',
                'unit_price' => $order->getShippingTotal(),
                'total_amount' => $order->getShippingTotal(),
                'merchant_data' => null,
                'type' => KlarnaDataInterface::ORDER_LINE_TYPE_SHIPPING_FEE,
            ];
        }

        return $data;
    }

    public function isAnonymous(): bool
    {
        return true;
    }
}
