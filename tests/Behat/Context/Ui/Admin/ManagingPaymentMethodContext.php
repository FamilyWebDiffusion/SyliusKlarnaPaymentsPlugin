<?php

declare(strict_types=1);

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;

class ManagingPaymentMethodContext implements Context
{
    /** @var CreatePageInterface */
    private $createPage;

    public function __construct(CreatePageInterface $createPage)
    {
        $this->createPage = $createPage;
    }

    /**
     * @Given I want to create a new Klarna payment method
     */
    public function iWantToCreateANewKlarnaPaymentMethod(): void
    {
        $this->createPage->open(['factory' => 'klarna_payments']);
    }

    /**
     * @When I fill the API username with :apiUsername
     */
    public function iFillTheAPIUsernameWith(string $apiUsername): void
    {
        $this->createPage->setApiUsername($apiUsername);
    }

    /**
     * @When I fill the API password with :apiPassword
     */
    public function iFillTheAPIPasswordWith(string $apiPassword): void
    {
        $this->createPage->setApiPassword($apiPassword);
    }

    /**
     * @When I select SandboxMode
     */
    public function iSelectSandBoxMode(): void
    {
        $this->createPage->setSandboxMode(true);
    }

    /**
     * @When I unselect SandboxMode
     */
    public function iUnselectSandBoxMode(): void
    {
        $this->createPage->setSandboxMode(false);
    }

    /**
     * @When I select :zone as Klarna account zone
     */
    public function iSelectZoneAsKlarnaAccountZone(string $zone): void
    {
        $this->createPage->selectApiZone($zone);
    }

    /**
     * @When I select :method as Klarna Payment Method
     */
    public function iSelectMethodAsKlarnaPaymentMethod(string $method): void
    {
        $this->createPage->selectPaymentMethod($method);
    }
}
