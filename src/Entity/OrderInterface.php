<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;

interface OrderInterface extends BaseOrderInterface
{
    public function getKlarnaSessionId(): ?string;

    public function deleteKlarnaSessionId(): void;

    public function setKlarnaSessionId(?string $klarnaSessionId): void;
}
