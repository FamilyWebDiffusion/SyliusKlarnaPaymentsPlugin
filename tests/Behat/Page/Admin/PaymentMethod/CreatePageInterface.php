<?php

declare(strict_types=1);

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Behat\Page\Admin\PaymentMethod;

interface CreatePageInterface
{
    public function setApiUsername(string $apiUsername): void;

    public function setApiPassword(string $apiPassword): void;

    public function setSandboxMode(bool $isSandbox): void;

    public function selectApiZone(string $zone): void;

    public function selectPaymentMethod(string $method): void;
}
