<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

class KlarnaPaymentsSession
{
    private string $clientToken;

    private ?string $sessionId = null;

    /** @var array<int, array> */
    private array $paymentMethodCategories = [];

    /** @var array<string> */
    private array $paymentMethodCategoriesIdentifiers = [];

    /** @var array<string,array> */
    private array $paymentMethodCategoriesAssociative = [];

    private ?string $authorizationCallbackUrl = null;

    private ?string $authorizationToken = null;

    public function isCreated(): bool
    {
        return isset($this->clientToken);
    }

    public function getClientToken(): string
    {
        return $this->clientToken;
    }

    public function setClientToken(string $clientToken): void
    {
        $this->clientToken = $clientToken;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getAuthorizationCallbackUrl(): ?string
    {
        return $this->authorizationCallbackUrl;
    }

    public function setAuthorizationCallbackUrl(?string $authorizationCallbackUrl): void
    {
        $this->authorizationCallbackUrl = $authorizationCallbackUrl;
    }

    /**
     * @return array<array>
     */
    public function getPaymentMethodCategories(): array
    {
        return $this->paymentMethodCategories;
    }

    /**
     * @return string[]
     */
    public function getPaymentMethodCategoriesIdentifiers(): array
    {
        return $this->paymentMethodCategoriesIdentifiers;
    }

    /**
     * @param array<int, array> $paymentMethodCategories
     */
    public function setPaymentMethodCategories(array $paymentMethodCategories): void
    {
        $this->paymentMethodCategories = $paymentMethodCategories;
        $this->paymentMethodCategoriesIdentifiers = [];
        $this->paymentMethodCategoriesAssociative = [];
        foreach ($paymentMethodCategories as $id => $paymentMethodCategory) {
            $this->paymentMethodCategoriesIdentifiers[$id] = $paymentMethodCategory['identifier'];
            $this->paymentMethodCategoriesAssociative[$paymentMethodCategory['identifier']] = $paymentMethodCategory;
        }
    }

    public function getStandardAssetUrl(string $identifier): ?string
    {
        if (!\array_key_exists($identifier, $this->paymentMethodCategoriesAssociative)) {
            return null;
        }

        return $this->paymentMethodCategoriesAssociative[$identifier]['asset_urls']['standard'];
    }

    public function getDescriptiveAssetUrl(string $identifier): ?string
    {
        if (!\array_key_exists($identifier, $this->paymentMethodCategoriesAssociative)) {
            return null;
        }

        return $this->paymentMethodCategoriesAssociative[$identifier]['asset_urls']['descriptive'];
    }

    public function getAuthorizationToken(): ?string
    {
        return $this->authorizationToken;
    }

    public function setAuthorizationToken(?string $authorizationToken): void
    {
        $this->authorizationToken = $authorizationToken;
    }
}
