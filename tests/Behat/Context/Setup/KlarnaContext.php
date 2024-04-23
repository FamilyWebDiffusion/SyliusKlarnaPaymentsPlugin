<?php

declare(strict_types=1);

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaPaymentsGatewayFactory;
use Sylius\Behat\Page\Shop\Checkout\SelectShippingPageInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Behat\Mocker\KlarnaPaymentsApiClientMocker;

class KlarnaContext implements Context
{
    private ExampleFactoryInterface $paymentMethodExampleFactory;

    private SharedStorageInterface $sharedStorage;

    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    private EntityManagerInterface $paymentMethodManager;

    private KlarnaPaymentsApiClientMocker $klarnaPaymentsApiClientMocker;

    private SelectShippingPageInterface $selectShippingPage;

    public function __construct(
        ExampleFactoryInterface $paymentMethodExampleFactory,
        SharedStorageInterface $sharedStorage,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        EntityManagerInterface $paymentMethodManager,
        KlarnaPaymentsApiClientMocker $klarnaPaymentsApiClientMocker,
        SelectShippingPageInterface $selectShippingPage,
    ) {
        $this->paymentMethodExampleFactory = $paymentMethodExampleFactory;
        $this->sharedStorage = $sharedStorage;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodManager = $paymentMethodManager;
        $this->klarnaPaymentsApiClientMocker = $klarnaPaymentsApiClientMocker;
        $this->selectShippingPage = $selectShippingPage;
    }

    /**
     * @Given the store has a payment method :paymentMethodName with a code :paymentMethodCode and Klarna payment PAY LATER gateway
     */
    public function theStoreHasAPaymentMethodWithACodeAndKlarnaPaymentGateway(string $paymentMethodName, string $paymentMethodCode): void
    {
        $paymentMethod = $this->createPaymentMethod(
            $paymentMethodName,
            $paymentMethodCode,
            KlarnaPaymentsGatewayFactory::NAME,
            'Klarna',
        );

        $paymentMethod->getGatewayConfig()->setConfig([
            KlarnaGatewayConfigInterface::CONFIG_API_USERNAME => 'test',
            KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD => 'test',
            KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX => true,
            KlarnaGatewayConfigInterface::CONFIG_FACTORY_NAME => KlarnaPaymentsGatewayFactory::NAME,
            KlarnaGatewayConfigInterface::CONFIG_API_ZONE => KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE,
            KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD => 'pay_later',
            'use_authorize' => true,
        ]);

        $this->paymentMethodManager->flush();
    }

    /**
     * @Given /^I complete the shipping step and klarna session could be initialized$/
     */
    public function iCompleteShippingStepAndKlarnaSessionIsInitialized()
    {
        $this->klarnaPaymentsApiClientMocker->mockCreateKlarnaPaymentPayLaterSession(function () {
            $this->selectShippingPage->nextStep();
        });
    }

    /**
     * @Given /^I complete the shipping step and klarna session could not be initialized$/
     */
    public function iCompleteShippingStepAndKlarnaSessionIsUnsuccessfull()
    {
        $this->klarnaPaymentsApiClientMocker->mockCantCreateKlarnaPaymentSession(function () {
            $this->selectShippingPage->nextStep();
        });
    }

    private function createPaymentMethod(
        string $name,
        string $code,
        string $factoryName,
        string $description = '',
        bool $addForCurrentChannel = true,
        int $position = null,
    ): PaymentMethodInterface {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst($name),
            'code' => $code,
            'description' => $description,
            'gatewayName' => $factoryName,
            'gatewayFactory' => $factoryName,
            'enabled' => true,
            'channels' => ($addForCurrentChannel && $this->sharedStorage->has('channel')) ? [$this->sharedStorage->get('channel')] : [],
        ]);
        if (null !== $position) {
            $paymentMethod->setPosition($position);
        }

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);

        return $paymentMethod;
    }
}
