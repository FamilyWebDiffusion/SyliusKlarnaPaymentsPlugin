<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\Helper;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class EuAndAustraliaTaxDataHelper extends TaxDataHelper
{
    public static function addOrderItemTax(array &$data, PaymentInterface $payment): void
    {
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $data['order_tax_amount'] = $order->getTaxTotal();

        Assert::isArray($data['order_lines']);

        $shippingId = null;
        $sumOrderLineTaxes = 0;

        /** @var array<int, array> $orderLines */
        $orderLines = $data['order_lines'];
        foreach ($orderLines as $id => $orderLine) {
            //we have to find if tax is included in price or not
            $item = self::findOrderItem($order, (int) $orderLine['merchant_data']);

            if ($item === null) {
                if ($orderLine['type'] === KlarnaDataInterface::ORDER_LINE_TYPE_SHIPPING_FEE) {
                    $shippingId = $id;
                }

                continue;
            }

            if (self::isTaxIncluded($item)) {
                // If tax is included in the price, remove tax from it
                $orderLine['unit_price'] = $item->getUnitPrice();
            } else {
                $orderLine['unit_price'] = (int) \floor($item->getUnitPrice() + $item->getTaxTotal() / $item->getQuantity());
            }

            $orderLine['total_amount'] = $item->getTotal();
            $orderLine['total_tax_amount'] = $item->getTaxTotal();
            $orderLine['tax_rate'] = self::getTaxRate($orderLine['total_amount'], $orderLine['total_tax_amount']);

            $data['order_lines'][$id] = $orderLine;

            $sumOrderLineTaxes += $item->getTaxTotal();
        }

        // And now, check if shipping fees are also taxed (sylius is changing shipping cost accordingly)
        if ($sumOrderLineTaxes < $order->getTaxTotal() && is_int($shippingId)) {
            $shipping_tax = $order->getTaxTotal() - $sumOrderLineTaxes;
            $data['order_lines'][$shippingId]['total_tax_amount'] = $shipping_tax;
            $data['order_lines'][$shippingId]['tax_rate'] = self::getTaxRate(
                $data['order_lines'][$shippingId]['total_amount'],
                $shipping_tax,
            );

            $sumOrderLineTaxes += $shipping_tax;
        }

        // If the order total tax is different from the sum of all order_lines tax amount:
        // something got wrong, we could not calculate tax, so remove all taxes to send a minimal data order
        if ($sumOrderLineTaxes !== $order->getTaxTotal()) {
            self::removeAllTaxes($data);
        }
    }

    // Klarna tax rate is send in per 10000 (i.e. 20% -> 2000)
    private static function getTaxRate(int $priceWithTax, int $taxAmount): int
    {
        if ($priceWithTax === 0) {
            return 0;
        }

        return 10 * (int) \round($taxAmount * 1000 / ($priceWithTax - $taxAmount));
    }

    /**
     * @param array<string, array|int|string|null> $data
     */
    private static function removeAllTaxes(array &$data): void
    {
        /** @var array<int, array> $orderLines */
        $orderLines = $data['order_lines'];
        foreach ($orderLines as $id => $orderLine) {
            unset($orderLines[$id]['total_tax_amount'], $orderLines[$id]['tax_rate'], $orderLines[$id]['total_discount_amount']);

            $orderLines[$id]['unit_price'] = (int) \round($orderLines[$id]['total_amount'] / $orderLines[$id]['quantity']);
        }
        $data['order_lines'] = $orderLines;
        unset($data['order_tax_amount']);
    }
}
