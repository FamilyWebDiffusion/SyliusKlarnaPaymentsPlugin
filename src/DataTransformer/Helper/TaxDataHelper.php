<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\Helper;

use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;

abstract class TaxDataHelper
{
    /**
     * @param array<string, array|int|string|null> $data
     */
    abstract public static function addOrderItemTax(array &$data, PaymentInterface $payment): void;

    protected static function findOrderItem(OrderInterface $order, ?int $orderItemId): ?OrderItemInterface
    {
        foreach ($order->getItems() as $item) {
            if ($item->getId() === $orderItemId) {
                return $item;
            }
        }

        return null;
    }

    protected static function isTaxIncluded(OrderItemInterface $orderItem): bool
    {
        foreach ($orderItem->getUnits() as $unit) {
            foreach ($unit->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT) as $taxAdjustment) {
                if ($taxAdjustment->isNeutral()) {
                    return true;
                }
            }
        }

        return false;
    }
}
