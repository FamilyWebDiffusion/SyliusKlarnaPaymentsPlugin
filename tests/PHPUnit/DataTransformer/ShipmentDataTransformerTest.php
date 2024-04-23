<?php

declare(strict_types=1);


namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\PHPUnit\DataTransformer;


use Doctrine\Common\Collections\ArrayCollection;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\ShipmentDataTransformer;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\TaxDataTransformer;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\Payment;

class ShipmentDataTransformerTest extends TestCase
{
    /**
     * @dataProvider importOrders
     */
    public function testFormatShippingTaxes($dataExpected, $initialData, $payment)
    {
        $shipmentDataTransformer = new ShipmentDataTransformer();
        $taxDataTransformer = new TaxDataTransformer();

        $data = ($shipmentDataTransformer)($initialData, $payment);
        $data = ($taxDataTransformer)($data, $payment);

        self::assertSame($dataExpected, $data );

    }

    public function importOrders(): array
    {
        $taxIncludedForDE = $this->taxIncludedForDE();
        $taxExcludedForDE = $this->taxExcludedForDE();

        return [
            // Order from DE with Tax included
            [
                $taxIncludedForDE['data'],
                $taxIncludedForDE['initial_data'],
                $taxIncludedForDE['payment']
            ],
            // Order from DE with Tax excluded
            [
                $taxExcludedForDE['data'],
                $taxExcludedForDE['initial_data'],
                $taxExcludedForDE['payment']
            ],
        ];
    }


    private function taxIncludedForDE():array
    {
        $billingAddress = $this->createMock(AddressInterface::class);
        $billingAddress->method('getCountryCode')->willReturn('DE');

        $order = $this->createMock(Order::class);
        $order->method('getBillingAddress')->willReturn($billingAddress);

        $order->method('getTaxTotal')->willReturn(433);

        $unit = $this->createMock(OrderItemUnit::class);
        $adjustment = $this->createMock(AdjustmentInterface::class);
        $adjustment->method('getType')->willReturn(AdjustmentInterface::TAX_ADJUSTMENT);
        $adjustment->method('isNeutral')->willReturn(true);
        $unit->method('getAdjustments')->willReturn(new ArrayCollection([$adjustment]));

        $item = $this->createMock(OrderItem::class);
        $item->method('getUnitPrice')->willReturn(2000);
        $item->method('getQuantity')->willReturn(1);
        $item->method('getId')->willReturn(21);
        $item->method('getFullDiscountedUnitPrice')->willReturn(2000);
        $item->method('getTaxTotal')->willReturn(333);
        $item->method('getTotal')->willReturn(2000);
        $item->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $order->method('getItems')->willReturn(new ArrayCollection([$item]));
        $order->method('getShippingTotal')->willReturn(600);

        $payment= $this->createMock(Payment::class);
        $payment
            ->method('getOrder')
            ->willReturn($order);

        $initialData = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 1,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 2000,
                    'merchant_data' => 21,
                    'total_discount_amount' => 0,
                    'type' => 'physical',
                ]
            ]
        ];

        $data = [
            'order_lines' => [
                [
                  'name' => 'Knitted white pompom cap',
                  'quantity' => 1,
                  'reference' => 'Knitted_white_pompom_cap-variant-0',
                  'unit_price' => 2000,
                  'merchant_data' => 21,
                  'total_discount_amount' => 0,
                  'type' => 'physical',
                  'total_amount' => 2000,
                  'total_tax_amount' => 333,
                  'tax_rate' => 2000,
                ],
                [
                  'quantity' => 1,
                  'name' => 'Shipping Fee',
                  'unit_price' => 600,
                  'total_amount' => 600,
                  'merchant_data' => null,
                  'type' => 'shipping_fee',
                  'total_tax_amount' => 100,
                  'tax_rate' => 2000,
                ]
            ],
            'order_tax_amount' => 433
        ];

        return [
            'data' => $data,
            'payment' => $payment,
            'initial_data' => $initialData
        ];
    }

    private function taxExcludedForDE():array
    {
        $billingAddress = $this->createMock(AddressInterface::class);
        $billingAddress->method('getCountryCode')->willReturn('DE');

        $order = $this->createMock(Order::class);
        $order->method('getBillingAddress')->willReturn($billingAddress);

        $order->method('getTaxTotal')->willReturn(520);

        $unit = $this->createMock(OrderItemUnit::class);
        $adjustment = $this->createMock(AdjustmentInterface::class);
        $adjustment->method('getType')->willReturn(AdjustmentInterface::TAX_ADJUSTMENT);
        $adjustment->method('isNeutral')->willReturn(false);
        $unit->method('getAdjustments')->willReturn(new ArrayCollection([$adjustment]));

        $item = $this->createMock(OrderItem::class);
        $item->method('getUnitPrice')->willReturn(2000);
        $item->method('getQuantity')->willReturn(1);
        $item->method('getId')->willReturn(21);
        $item->method('getFullDiscountedUnitPrice')->willReturn(2000);
        $item->method('getTaxTotal')->willReturn(400);
        $item->method('getTotal')->willReturn(2400);
        $item->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $order->method('getItems')->willReturn(new ArrayCollection([$item]));
        $order->method('getShippingTotal')->willReturn(720);

        $payment= $this->createMock(Payment::class);
        $payment
            ->method('getOrder')
            ->willReturn($order);

        $initialData = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 1,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 2000,
                    'merchant_data' => 21,
                    'total_discount_amount' => 0,
                    'type' => 'physical',
                ]
            ]
        ];

        $data = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 1,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 2400,
                    'merchant_data' => 21,
                    'total_discount_amount' => 0,
                    'type' => 'physical',
                    'total_amount' => 2400,
                    'total_tax_amount' => 400,
                    'tax_rate' => 2000,
                ],
                [
                    'quantity' => 1,
                    'name' => 'Shipping Fee',
                    'unit_price' => 720,
                    'total_amount' => 720,
                    'merchant_data' => null,
                    'type' => 'shipping_fee',
                    'total_tax_amount' => 120,
                    'tax_rate' => 2000,
                ]
            ],
            'order_tax_amount' => 520
        ];

        return [
            'data' => $data,
            'payment' => $payment,
            'initial_data' => $initialData
        ];
    }

}
