<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use Sylius\Component\Core\Model\PaymentInterface;

interface ShippingInfoDataTransformerInterface
{
    /**
     * @return array<string, string|array>
     */
    public function transform(PaymentInterface $payment): array;
}
