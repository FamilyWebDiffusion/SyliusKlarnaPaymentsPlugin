<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\Helper;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class UsTaxDataHelper extends TaxDataHelper
{
    public static function addOrderItemTax(array &$data, PaymentInterface $payment): void
    {
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        //For US, tax is not included in each order_line, but have a separate entry into 'order_lines'
        $shippingId = null;
        $sumOrderLineTotal = 0;

        Assert::isArray($data['order_lines']);

        /** @var array<int, array> $orderLines */
        $orderLines = $data['order_lines'];
        foreach ($orderLines as $id => $orderLine) {
            //we have to find if tax is included in price or not
            $item = self::findOrderItem($order, (int) $orderLine['merchant_data']);

            if ($item === null) {
                if ($orderLine['type'] == KlarnaDataInterface::ORDER_LINE_TYPE_SHIPPING_FEE) {
                    $shippingId = $id;
                }

                continue;
            }

            if (self::isTaxIncluded($item)) {
                // If tax is included in the price, remove tax from it
                $orderLine['unit_price'] = (int) \floor($item->getUnitPrice() - ($item->getTaxTotal() / $item->getQuantity()));
                $orderLine['total_amount'] = $item->getFullDiscountedUnitPrice() * $item->getQuantity() - $item->getTaxTotal();
            } else {
                $orderLine['unit_price'] = $item->getUnitPrice();
                $orderLine['total_amount'] = $item->getTotal() - $item->getTaxTotal();
            }

            $data['order_lines'][$id] = $orderLine;

            $sumOrderLineTotal += $orderLine['total_amount'];
        }

        $tax_total = $order->getTaxTotal();
        $data['order_tax_amount'] = $tax_total;

        $data['order_lines'][] = [
            'type' => KlarnaDataInterface::ORDER_LINE_TYPE_SALES_TAX,
            'name' => 'Tax',
            'quantity' => 1,
            'unit_price' => $tax_total,
            'total_amount' => $tax_total,
        ];

        // If Sylius calculate tax on shipping, we need to exclude them from shipping cost
        if ($order->getTotal() < $sumOrderLineTotal + $order->getShippingTotal() + $tax_total && is_int($shippingId)) {
            $data['order_lines'][$shippingId]['unit_price'] =
            $data['order_lines'][$shippingId]['total_amount'] =
                $order->getTotal() - $sumOrderLineTotal - $tax_total;
        }
    }
}
