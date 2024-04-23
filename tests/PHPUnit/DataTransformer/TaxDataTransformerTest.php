<?php

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\PHPUnit\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\TaxDataTransformer;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\Payment;
use PHPUnit\Framework\TestCase;

class TaxDataTransformerTest extends TestCase
{
    /**
     * @dataProvider importOrders
     */
    public function testFormatTaxes($dataExpected, $initialData, $payment)
    {
        $taxDataTransformer = new TaxDataTransformer();

        $data = ($taxDataTransformer)($initialData, $payment);

        self::assertSame($dataExpected, $data );

    }

    public function importOrders(): array
    {
        return [
            // Order from US with Tax excluded
            [
                $this->TaxExcludedForUS()['data'],
                $this->TaxExcludedForUS()['initial_data'],
                $this->TaxExcludedForUS()['payment']
            ],
            //Order from US with Tax included
            [
                $this->TaxIncludedForUS()['data'],
                $this->TaxIncludedForUS()['initial_data'],
                $this->TaxIncludedForUS()['payment']
            ],
            //Order from US with Tax excluded with several items
            [
                $this->SeveralItemsDiscountTaxExcludedForUS()['data'],
                $this->SeveralItemsDiscountTaxExcludedForUS()['initial_data'],
                $this->SeveralItemsDiscountTaxExcludedForUS()['payment']
            ],
            //Order from US with Tax included and discount
            [
                $this->DiscountTaxIncludedForUS()['data'],
                $this->DiscountTaxIncludedForUS()['initial_data'],
                $this->DiscountTaxIncludedForUS()['payment']
            ],
            //Order from DE with Special Tax included
            [
                $this->TaxIncludedForDESpecialVatTaxRate()['data'],
                $this->TaxIncludedForDESpecialVatTaxRate()['initial_data'],
                $this->TaxIncludedForDESpecialVatTaxRate()['payment']
            ],
            //Order from DE with Tax included
            [
                $this->TaxIncludedForDE()['data'],
                $this->TaxIncludedForDE()['initial_data'],
                $this->TaxIncludedForDE()['payment']
            ],
            //Order from DE with tax included and 2 tax rate
            [
                $this->TaxIncludedForDESpecialAndNormalVatTaxRate()['data'],
                $this->TaxIncludedForDESpecialAndNormalVatTaxRate()['initial_data'],
                $this->TaxIncludedForDESpecialAndNormalVatTaxRate()['payment']
            ],
            //Order from DE with Tax included and discount
            [
                $this->DiscountTaxIncludedForDE()['data'],
                $this->DiscountTaxIncludedForDE()['initial_data'],
                $this->DiscountTaxIncludedForDE()['payment']
            ],
            //Order from DE with Tax included and discount for several items
            [
                $this->SeveralItemsDiscountTaxIncludedForDE()['data'],
                $this->SeveralItemsDiscountTaxIncludedForDE()['initial_data'],
                $this->SeveralItemsDiscountTaxIncludedForDE()['payment']
            ],
            //Order from DE with Tax excluded
            [
                $this->TaxExcludedForDE()['data'],
                $this->TaxExcludedForDE()['initial_data'],
                $this->TaxExcludedForDE()['payment']
            ],
            //Order from DE with Tax excluded
            [
                $this->SeveralItemsDiscountTaxExcludedForDE()['data'],
                $this->SeveralItemsDiscountTaxExcludedForDE()['initial_data'],
                $this->SeveralItemsDiscountTaxExcludedForDE()['payment']
            ],
        ];
    }


    private function TaxExcludedForUS() : array
    {
        $order = $this->getBaseOrderWithCountryUS();
        [$payment, $initialData] = $this->OrderOneItemTaxExcluded($order);

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
                ],
                [
                    'type' => 'sales_tax',
                    'name' => 'Tax',
                    'quantity' => 1,
                    'unit_price' => 400,
                    'total_amount' => 400,
                ],
            ],
            "order_tax_amount" => 400
        ];


        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }

    private function SeveralItemsDiscountTaxExcludedForUS() : array
    {
        $order = $this->getBaseOrderWithCountryUS();
        [$payment, $initialData] = $this->OrderDiscountSeveralItemsTaxExcluded($order);

        $data = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 2,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 2000,
                    'merchant_data' => 21,
                    'total_discount_amount' => 444,
                    'type' => 'physical',
                    'total_amount' => 3556,
                ],
                [
                    'name' => 'Ribbed copper slim fit Tee',
                    'quantity' => 1,
                    'reference' => 'Ribbed_copper_slim_fit_Tee-variant-0',
                    'unit_price' => 500,
                    'merchant_data' => 20,
                    'total_discount_amount' => 56,
                    'type' => 'physical',
                    'total_amount' => 444,
                ],
                [
                    'type' => 'sales_tax',
                    'name' => 'Tax',
                    'quantity' => 1,
                    'unit_price' => 800,
                    'total_amount' => 800,
                ],
            ],
            "order_tax_amount" => 800
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }

    private function TaxIncludedForUS() : array
    {
        $order = $this->getBaseOrderWithCountryUS();
        [$payment, $initialData] = $this->OrderOneItemTaxIncluded($order);

        $data = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 1,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 1667,
                    'merchant_data' => 21,
                    'total_discount_amount' => 0,
                    'type' => 'physical',
                    'total_amount' => 1667,
                ],
                [
                    'type' => 'sales_tax',
                    'name' => 'Tax',
                    'quantity' => 1,
                    'unit_price' => 333,
                    'total_amount' => 333,
                ],
            ],
            "order_tax_amount" => 333
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }

    private function DiscountTaxIncludedForUS() : array
    {
        $order = $this->getBaseOrderWithCountryUS();
        [$payment, $initialData] = $this->OrderDiscountOneItemTaxIncluded($order);

        $data = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 1,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 1750,
                    'merchant_data' => 21,
                    'total_discount_amount' => 500,
                    'type' => 'physical',
                    'total_amount' => 1250,
                ],
                [
                    'type' => 'sales_tax',
                    'name' => 'Tax',
                    'quantity' => 1,
                    'unit_price' => 250,
                    'total_amount' => 250,
                ],
            ],
            "order_tax_amount" => 250
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }

    private function TaxIncludedForDE() : array
    {
        $order = $this->getBaseOrderWithCountryDE();
        [$payment, $initialData] = $this->OrderOneItemTaxIncluded($order);

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
                ]
            ],
            "order_tax_amount" => 333
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }

    private function TaxIncludedForDESpecialVatTaxRate() : array
    {
        $order = $this->getBaseOrderWithCountryDE();
        [$payment, $initialData] = $this->OrderOneItemSpecialTaxIncluded($order);

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
                    'total_tax_amount' => 104,
                    'tax_rate' => 550,
                ]
            ],
            "order_tax_amount" => 104
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }


    private function TaxIncludedForDESpecialAndNormalVatTaxRate() : array
    {
        $order = $this->getBaseOrderWithCountryDE();
        [$payment, $initialData] = $this->OrderTwoItemsSpecialTaxIncluded($order);

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
                    'total_tax_amount' => 104,
                    'tax_rate' => 550,
                ],
                [
                    'name' => 'Ribbed copper slim fit Tee',
                    'quantity' => 1,
                    'reference' => 'Ribbed_copper_slim_fit_Tee-variant-0',
                    'unit_price' => 500,
                    'merchant_data' => 20,
                    'total_discount_amount' => 0,
                    'type' => 'physical',
                    'total_amount' => 500,
                    'total_tax_amount' => 26,
                    'tax_rate' => 550,
                ]
            ],
            "order_tax_amount" => 130
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }

    private function DiscountTaxIncludedForDE() : array
    {
        $order = $this->getBaseOrderWithCountryDE();
        [$payment, $initialData] = $this->OrderDiscountOneItemTaxIncluded($order);

        $data = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 1,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 2000,
                    'merchant_data' => 21,
                    'total_discount_amount' => 500,
                    'type' => 'physical',
                    'total_amount' => 1500,
                    'total_tax_amount' => 250,
                    'tax_rate' => 2000,
                ]
            ],
            "order_tax_amount" => 250
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }

    private function SeveralItemsDiscountTaxIncludedForDE() : array
    {
        $order = $this->getBaseOrderWithCountryDE();
        [$payment, $initialData] = $this->OrderDiscountSeveralItemsTaxIncluded($order);

        $data = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 2,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 2000,
                    'merchant_data' => 21,
                    'total_discount_amount' => 444,
                    'type' => 'physical',
                    'total_amount' => 3556,
                    'total_tax_amount' => 593,
                    'tax_rate' => 2000,
                ],
                [
                'name' => 'Ribbed copper slim fit Tee',
                'quantity' => 1,
                'reference' => 'Ribbed_copper_slim_fit_Tee-variant-0',
                'unit_price' => 500,
                'merchant_data' => 20,
                'total_discount_amount' => 56,
                'type' => 'physical',
                'total_amount' => 444,
                'total_tax_amount' => 74,
                'tax_rate' => 2000,
                ]
            ],
            "order_tax_amount" => 667
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }

    private function TaxExcludedForDE() : array
    {
        $order = $this->getBaseOrderWithCountryDE();
        [$payment, $initialData] = $this->OrderOneItemTaxExcluded($order);

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
                ]
            ],
            "order_tax_amount" => 400
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }

    private function SeveralItemsDiscountTaxExcludedForDE() : array
    {
        $order = $this->getBaseOrderWithCountryDE();
        [$payment, $initialData] = $this->OrderDiscountSeveralItemsTaxExcluded($order);

        $data = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 2,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 2355,
                    'merchant_data' => 21,
                    'total_discount_amount' => 444,
                    'type' => 'physical',
                    'total_amount' => 4267,
                    'total_tax_amount' => 711,
                    'tax_rate' => 2000,
                ],
                [
                    'name' => 'Ribbed copper slim fit Tee',
                    'quantity' => 1,
                    'reference' => 'Ribbed_copper_slim_fit_Tee-variant-0',
                    'unit_price' => 589,
                    'merchant_data' => 20,
                    'total_discount_amount' => 56,
                    'type' => 'physical',
                    'total_amount' => 533,
                    'total_tax_amount' => 89,
                    'tax_rate' => 2000,
                ],
            ],
            "order_tax_amount" => 800
        ];

        return ['data' => $data, 'initial_data' => $initialData, 'payment' => $payment];
    }


    private function OrderOneItemTaxIncluded($order): array
    {
        $order->method('getTaxTotal')->willReturn(333);

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

        return [$payment, $initialData];
    }

    private function OrderOneItemSpecialTaxIncluded($order): array
    {
        $order->method('getTaxTotal')->willReturn(104);

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
        $item->method('getTaxTotal')->willReturn(104);
        $item->method('getTotal')->willReturn(2000);
        $item->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $order->method('getItems')->willReturn(new ArrayCollection([$item]));

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

        return [$payment, $initialData];
    }

    private function OrderTwoItemsSpecialTaxIncluded($order): array
    {
        $order->method('getTaxTotal')->willReturn(130);

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
        $item->method('getTaxTotal')->willReturn(104);
        $item->method('getTotal')->willReturn(2000);
        $item->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $item2 = $this->createMock(OrderItem::class);
        $item2->method('getUnitPrice')->willReturn(500);
        $item2->method('getQuantity')->willReturn(1);
        $item2->method('getId')->willReturn(20);
        $item2->method('getFullDiscountedUnitPrice')->willReturn(500);
        $item2->method('getTaxTotal')->willReturn(26);
        $item2->method('getTotal')->willReturn(500);
        $item2->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $order->method('getItems')->willReturn(new ArrayCollection([$item, $item2]));

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
                ],
                [
                    'name' => 'Ribbed copper slim fit Tee',
                    'quantity' => 1,
                    'reference' => 'Ribbed_copper_slim_fit_Tee-variant-0',
                    'unit_price' => 500,
                    'merchant_data' => 20,
                    'total_discount_amount' => 0,
                    'type' => 'physical',
                ]
            ]
        ];

        return [$payment, $initialData];
    }


    private function OrderOneItemTaxExcluded($order): array
    {

        $order->method('getTaxTotal')->willReturn(400);


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

        return [$payment, $initialData];
    }

    private function OrderDiscountSeveralItemsTaxExcluded($order): array
    {
        $order->method('getTaxTotal')->willReturn(800);

        $unit = $this->createMock(OrderItemUnit::class);
        $adjustment = $this->createMock(AdjustmentInterface::class);
        $adjustment->method('getType')->willReturn(AdjustmentInterface::TAX_ADJUSTMENT);
        $adjustment->method('isNeutral')->willReturn(false);
        $unit->method('getAdjustments')->willReturn(new ArrayCollection([$adjustment]));

        $item = $this->createMock(OrderItem::class);
        $item->method('getUnitPrice')->willReturn(2000);
        $item->method('getQuantity')->willReturn(2);
        $item->method('getId')->willReturn(21);
        $item->method('getFullDiscountedUnitPrice')->willReturn(1778);
        $item->method('getTaxTotal')->willReturn(711);
        $item->method('getTotal')->willReturn(4267);
        $item->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $item2 = $this->createMock(OrderItem::class);
        $item2->method('getUnitPrice')->willReturn(500);
        $item2->method('getQuantity')->willReturn(1);
        $item2->method('getId')->willReturn(20);
        $item2->method('getFullDiscountedUnitPrice')->willReturn(444);
        $item2->method('getTaxTotal')->willReturn(89);
        $item2->method('getTotal')->willReturn(533);
        $item2->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $order->method('getItems')->willReturn(new ArrayCollection([$item, $item2]));

        $payment= $this->createMock(Payment::class);
        $payment
            ->method('getOrder')
            ->willReturn($order);

        $initialData = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 2,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 2000,
                    'merchant_data' => 21,
                    'total_discount_amount' => 444,
                    'type' => 'physical',
                ],
                [
                    'name' => 'Ribbed copper slim fit Tee',
                    'quantity' => 1,
                    'reference' => 'Ribbed_copper_slim_fit_Tee-variant-0',
                    'unit_price' => 500,
                    'merchant_data' => 20,
                    'total_discount_amount' => 56,
                    'type' => 'physical',
                ]
            ]
        ];

        return [$payment, $initialData];
    }

    private function OrderDiscountOneItemTaxIncluded($order): array
    {
        $order->method('getTaxTotal')->willReturn(250);

        $unit = $this->createMock(OrderItemUnit::class);
        $adjustment = $this->createMock(AdjustmentInterface::class);
        $adjustment->method('getType')->willReturn(AdjustmentInterface::TAX_ADJUSTMENT);
        $adjustment->method('isNeutral')->willReturn(true);
        $unit->method('getAdjustments')->willReturn(new ArrayCollection([$adjustment]));


        $item = $this->createMock(OrderItem::class);
        $item->method('getUnitPrice')->willReturn(2000);
        $item->method('getQuantity')->willReturn(1);
        $item->method('getId')->willReturn(21);
        $item->method('getFullDiscountedUnitPrice')->willReturn(1500);
        $item->method('getTaxTotal')->willReturn(250);
        $item->method('getTotal')->willReturn(1500);
        $item->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $order->method('getItems')->willReturn(new ArrayCollection([$item]));

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
                    'total_discount_amount' => 500,
                    'type' => 'physical',
                ]
            ]
        ];
        return [$payment, $initialData];
    }

    private function OrderDiscountSeveralItemsTaxIncluded($order): array
    {
        $order->method('getTaxTotal')->willReturn(667);

        $unit = $this->createMock(OrderItemUnit::class);
        $adjustment = $this->createMock(AdjustmentInterface::class);
        $adjustment->method('getType')->willReturn(AdjustmentInterface::TAX_ADJUSTMENT);
        $adjustment->method('isNeutral')->willReturn(true);
        $unit->method('getAdjustments')->willReturn(new ArrayCollection([$adjustment]));

        $item = $this->createMock(OrderItem::class);
        $item->method('getUnitPrice')->willReturn(2000);
        $item->method('getQuantity')->willReturn(2);
        $item->method('getId')->willReturn(21);
        $item->method('getFullDiscountedUnitPrice')->willReturn(1778);
        $item->method('getTaxTotal')->willReturn(593);
        $item->method('getTotal')->willReturn(3556);
        $item->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $item2 = $this->createMock(OrderItem::class);
        $item2->method('getUnitPrice')->willReturn(500);
        $item2->method('getQuantity')->willReturn(1);
        $item2->method('getId')->willReturn(20);
        $item2->method('getFullDiscountedUnitPrice')->willReturn(444);
        $item2->method('getTaxTotal')->willReturn(74);
        $item2->method('getTotal')->willReturn(444);
        $item2->method('getUnits')->willReturn(new ArrayCollection([$unit]));

        $order->method('getItems')->willReturn(new ArrayCollection([$item, $item2]));

        $payment= $this->createMock(Payment::class);
        $payment
            ->method('getOrder')
            ->willReturn($order);

        $initialData = [
            'order_lines' => [
                [
                    'name' => 'Knitted white pompom cap',
                    'quantity' => 2,
                    'reference' => 'Knitted_white_pompom_cap-variant-0',
                    'unit_price' => 2000,
                    'merchant_data' => 21,
                    'total_discount_amount' => 444,
                    'type' => 'physical',
                ],
                [
                    'name' => 'Ribbed copper slim fit Tee',
                    'quantity' => 1,
                    'reference' => 'Ribbed_copper_slim_fit_Tee-variant-0',
                    'unit_price' => 500,
                    'merchant_data' => 20,
                    'total_discount_amount' => 56,
                    'type' => 'physical',
                ]
            ]
        ];

        return [$payment, $initialData];
    }

    private function getBaseOrderWithCountryUS()
    {
        $billingAddress = $this->createMock(AddressInterface::class);
        $billingAddress->method('getCountryCode')->willReturn('US');

        $order = $this->createMock(Order::class);
        $order->method('getBillingAddress')->willReturn($billingAddress);

        return $order;
    }

    private function getBaseOrderWithCountryDE()
    {
        $billingAddress = $this->createMock(AddressInterface::class);
        $billingAddress->method('getCountryCode')->willReturn('DE');

        $order = $this->createMock(Order::class);
        $order->method('getBillingAddress')->willReturn($billingAddress);

        return $order;
    }
}
