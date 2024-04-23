<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentDataTransformerInterface
{
    /**
     * @param array<mixed> $data
     *
     * @return array<string, string|array>
     */
    public function transform(array $data, PaymentInterface $payment): array;

    /**
     * @param array<mixed> $data
     *
     * @return array<string, string|array>
     */
    public function transformAnonymized(array $data, PaymentInterface $payment): array;
}
