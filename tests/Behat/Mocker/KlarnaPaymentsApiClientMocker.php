<?php

declare(strict_types=1);

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Behat\Mocker;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaPaymentsSession;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaPaymentGatewayConfig;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClient;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Sylius\Behat\Service\Mocker\MockerInterface;

class KlarnaPaymentsApiClientMocker
{
    private MockerInterface $mocker;

    public function __construct(MockerInterface $mocker)
    {
        $this->mocker = $mocker;
    }

    public function mockCreateKlarnaPaymentPayLaterSession(callable $action): void
    {
        $this->mockApiCreateSessionPayLaterSuccessfullResponse();
        $action();
        $this->mocker->unmockAll();
    }

    public function mockCantCreateKlarnaPaymentSession(callable $action): void
    {
        $this->mockApiCreateSessionUnsuccessfullResponse();
        $action();
        $this->mocker->unmockAll();
    }

    private function mockApiCreateSessionPayLaterSuccessfullResponse(): void
    {
        $this->mockInitialize();
        $klarnaPaymentSession = new KlarnaPaymentsSession();
        $klarnaPaymentSession->setSessionId('a1adc3e2-ed2e-1ebe-8108-b2f3116f3928');
        $klarnaPaymentSession->setClientToken('eyJhbGciOiJSUzI1NiIsImtpZCI6IjgyMzA1ZWJjLWI4MTEtMzYzNy1hYTRjLTY2ZWNhMTg3NGYzZCJ9.eyJzZXNzaW9uX2lkIjoiYTFhZGMzZTItZWQyZS0xZWJlLTgxMDgtYjJmMzExNmYzOTI4IiwiYmFzZV91cmwiOiJodHRwczovL2pzLnBsYXlncm91bmQua2xhcm5hLmNvbS9ldS9rcC9sZWdhY3kvcGF5bWVudHMiLCJkZXNpZ24iOiJrbGFybmEiLCJsYW5ndWFnZSI6ImVuIiwicHVyY2hhc2VfY291bnRyeSI6IkRFIiwiZW52aXJvbm1lbnQiOiJwbGF5Z3JvdW5kIiwibWVyY2hhbnRfbmFtZSI6IllvdXIgYnVzaW5lc3MgbmFtZSIsInNlc3Npb25fdHlwZSI6IlBBWU1FTlRTIiwiY2xpZW50X2V2ZW50X2Jhc2VfdXJsIjoiaHR0cHM6Ly9ldS5wbGF5Z3JvdW5kLmtsYXJuYWV2dC5jb20iLCJleHBlcmltZW50cyI6W3sibmFtZSI6ImluLWFwcC1zZGstbmV3LWludGVybmFsLWJyb3dzZXIiLCJwYXJhbWV0ZXJzIjp7InZhcmlhdGVfaWQiOiJuZXctaW50ZXJuYWwtYnJvd3Nlci1lbmFibGUifX0seyJuYW1lIjoiaW4tYXBwLXNkay1jYXJkLXNjYW5uaW5nIiwicGFyYW1ldGVycyI6eyJ2YXJpYXRlX2lkIjoiY2FyZC1zY2FubmluZy1lbmFibGUifX1dfQ.bdoK1mRUoJl8BfNnzJCvqbxwZmLHKCYNJxBKxmQ67o9tLxJ6DTSncUAP-yAEhlJkN0QxGwumc8T-8RYc3ZMjQtDgRcoRSUAwm8jW_JPQEdDbqRhVib-AneEzsFviZIVlNxH3jjTtS7WoS6-FlXz1yJgR32wP1K2goyFLU_HXFlXV9T3zbI2tE2pryDjTHsvhz0xaC6qEEa4xvI-d54k3e15fYa1UhfqEZGb1ZfLQ20Emx0o4SFWQXkzupFOPwvlVaFx3sWbUAElG73E9h2NP9NO5dW9La8OY2QJc87L99X50tskcbmJHb6_iYDFF1Z7FSzNL6c6hOn0QIZyGqiLcOA');
        $klarnaPaymentSession->setPaymentMethodCategories([
            [
            'identifier' => 'pay_later',
            'name' => 'Pay later.',
            'asset_urls' => [
                'descriptive' => 'https://x.klarnacdn.net/payment-method/assets/badges/generic/klarna.svg',
                'standard' => 'https://x.klarnacdn.net/payment-method/assets/badges/generic/klarna.svg',
                ],
            ],
        ]);

        $this
            ->mocker
            ->mockService(
                KlarnaPaymentsApiClient::class,
                KlarnaPaymentsApiClientInterface::class,
            )
            ->shouldReceive('createAnonymousKlarnaPaymentSession')
            ->andReturn($klarnaPaymentSession)
        ;

        $this
            ->mocker
            ->mockService(
                KlarnaPaymentsApiClient::class,
                KlarnaPaymentsApiClientInterface::class,
            )
            ->shouldReceive('updateReturnUrlOnKlarnaPaymentSession')
        ;
    }

    private function mockApiCreateSessionUnsuccessfullResponse(): void
    {
        $this->mockInitialize();
        $klarnaPaymentSession = new KlarnaPaymentsSession();

        $this
            ->mocker
            ->mockService(
                KlarnaPaymentsApiClient::class,
                KlarnaPaymentsApiClientInterface::class,
            )
            ->shouldReceive('createAnonymousKlarnaPaymentSession')
            ->andReturn($klarnaPaymentSession)
        ;
    }

    private function mockInitialize(): void
    {
        $config = new ArrayObject([
            KlarnaGatewayConfigInterface::CONFIG_API_SERVER_PROD => [
                KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE => 'https://api.klarna.com/',
                KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA => 'https://api-na.klarna.com/',
                KlarnaGatewayConfigInterface::CONFIG_API_SERVER_OCEANIA => 'https://api-oc.klarna.com/',
            ],
            KlarnaGatewayConfigInterface::CONFIG_API_SERVER_TEST => [
                KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE => 'https://api.playground.klarna.com/',
                KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA => 'https://api-na.playground.klarna.com/',
                KlarnaGatewayConfigInterface::CONFIG_API_SERVER_OCEANIA => 'https://api-oc.playground.klarna.com/',
            ],
            KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_ENDPOINT => '/payments/v1/',
            KlarnaGatewayConfigInterface::CONFIG_API_ORDERMANAGMENT_ENDPOINT => '/ordermanagement/v1/orders/',
            KlarnaGatewayConfigInterface::CONFIG_DISPLAY_BIRTHDAY => true,

            KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX => true,
            KlarnaGatewayConfigInterface::CONFIG_API_ZONE => KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE,
            KlarnaGatewayConfigInterface::CONFIG_API_USERNAME => 'test',
            KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD => 'test',
            KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD => 'pay_later',
        ]);
        $klarnaPaymentGatewayConfig = new KlarnaPaymentGatewayConfig($config);

        $this
            ->mocker
            ->mockService(
                KlarnaPaymentsApiClient::class,
                KlarnaPaymentsApiClientInterface::class,
            )
            ->shouldReceive('initialize')
        ;

        $this
            ->mocker
            ->mockService(
                KlarnaPaymentsApiClient::class,
                KlarnaPaymentsApiClientInterface::class,
            )
            ->shouldReceive('getKlarnaPaymentGatewayConfig')
            ->andReturn($klarnaPaymentGatewayConfig)
        ;
    }
}
