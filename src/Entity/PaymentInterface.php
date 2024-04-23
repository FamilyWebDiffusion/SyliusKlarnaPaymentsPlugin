<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

use Sylius\Component\Core\Model\PaymentInterface as BasePaymentInterface;

interface PaymentInterface extends BasePaymentInterface
{
    public const AUTHORIZATION_TOKEN_KEY = 'authorization_token';

    public function setAuthorizationToken(string $klarnaAuthorisationToken): void;

    public function getAuthorizationToken(): ?string;
}
