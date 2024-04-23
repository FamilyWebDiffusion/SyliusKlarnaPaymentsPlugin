<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use Sylius\Component\Core\Model\PaymentInterface;

interface DataTransformerInterface
{
    /**
     * @param array<string, string|array|int|null> $data
     *
     * @return array<string, array|int|string|null>
     */
    public function __invoke(array $data, PaymentInterface $payment): array;

    public function isAnonymous(): bool;
}
