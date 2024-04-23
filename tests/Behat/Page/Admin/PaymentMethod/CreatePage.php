<?php

declare(strict_types=1);

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function setApiUsername(string $apiUsername): void
    {
        $this->getDocument()->fillField('API username', $apiUsername);
    }

    public function setApiPassword(string $apiPassword): void
    {
        $this->getDocument()->fillField('API password', $apiPassword);
    }

    public function setSandboxMode(bool $isSandbox): void
    {
        if ($isSandbox) {
            $this->getDocument()->checkField('Sandbox Mode');
        } else {
            $this->getDocument()->uncheckField('Sandbox Mode');
        }
    }

    public function selectApiZone(string $zone): void
    {
        $this->getDocument()->selectFieldOption('Your Klarna account zone', $zone);
    }

    public function selectPaymentMethod(string $method): void
    {
        $this->getDocument()->selectFieldOption('sylius_payment_method[gatewayConfig][config][payment_method]', $method);
    }
}
