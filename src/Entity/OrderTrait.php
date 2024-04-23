<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

trait OrderTrait
{
    private ?string $klarnaSessionId = null;

    public function getKlarnaSessionId(): ?string
    {
        return $this->klarnaSessionId;
    }

    public function deleteKlarnaSessionId(): void
    {
        $this->klarnaSessionId = null;
    }

    public function setKlarnaSessionId(?string $klarnaSessionId): void
    {
        $this->klarnaSessionId = $klarnaSessionId;
    }
}
