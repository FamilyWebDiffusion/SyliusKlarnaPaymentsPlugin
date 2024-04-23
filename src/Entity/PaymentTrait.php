<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

trait PaymentTrait
{
    public function setAuthorizationToken(string $klarnaAuthorisationToken): void
    {
        if ($this instanceof SyliusPaymentInterface) {
            $details = $this->getDetails();
            $details[PaymentInterface::AUTHORIZATION_TOKEN_KEY] = $klarnaAuthorisationToken;
            $this->setDetails($details);
        }
    }

    public function getAuthorizationToken(): ?string
    {
        if ($this instanceof SyliusPaymentInterface) {
            $details = $this->getDetails();
            if (isset($details[PaymentInterface::AUTHORIZATION_TOKEN_KEY])) {
                return $details[PaymentInterface::AUTHORIZATION_TOKEN_KEY];
            }
        }

        return null;
    }
}
