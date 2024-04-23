<?php

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\PHPUnit\ApiClient;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClient;

class KlarnaPaymentsApiClientTest extends WebTestCase
{

    private ?KlarnaPaymentsApiClient $klarnaPaymentsApiClient;

    public function setUp():void
    {
        self::bootKernel();
        $this->klarnaPaymentsApiClient = self::$container
            ->get(KlarnaPaymentsApiClient::class);

    }

    public function testDefaultConfigKlarnaPaymentFactory() : void
    {
        $config = [
            KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX => false,
            KlarnaGatewayConfigInterface::CONFIG_API_ZONE => KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE,
            KlarnaGatewayConfigInterface::CONFIG_API_USERNAME => 'test',
            KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD => 'test',
            KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD => 'pay_later',
        ];
        $this->klarnaPaymentsApiClient->initialize($config);
        self::assertEquals('https://api.klarna.com/payments/v1/', $this->klarnaPaymentsApiClient->getKlarnaPaymentsApiUrl());
        self::assertEquals('https://api.klarna.com/ordermanagement/v1/orders/', $this->klarnaPaymentsApiClient->getKlarnaOrderManagementApiUrl());

        $config[KlarnaGatewayConfigInterface::CONFIG_API_ZONE] = KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA;
        $this->klarnaPaymentsApiClient->initialize($config);
        self::assertEquals('https://api-na.klarna.com/payments/v1/', $this->klarnaPaymentsApiClient->getKlarnaPaymentsApiUrl());
        self::assertEquals('https://api-na.klarna.com/ordermanagement/v1/orders/', $this->klarnaPaymentsApiClient->getKlarnaOrderManagementApiUrl());

        $config[KlarnaGatewayConfigInterface::CONFIG_API_ZONE] = KlarnaGatewayConfigInterface::CONFIG_API_SERVER_OCEANIA;
        $this->klarnaPaymentsApiClient->initialize($config);
        self::assertEquals('https://api-oc.klarna.com/payments/v1/', $this->klarnaPaymentsApiClient->getKlarnaPaymentsApiUrl());
        self::assertEquals('https://api-oc.klarna.com/ordermanagement/v1/orders/', $this->klarnaPaymentsApiClient->getKlarnaOrderManagementApiUrl());

        $config[KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX] = true;
        $this->klarnaPaymentsApiClient->initialize($config);
        self::assertEquals('https://api-oc.playground.klarna.com/payments/v1/', $this->klarnaPaymentsApiClient->getKlarnaPaymentsApiUrl());
        self::assertEquals('https://api-oc.playground.klarna.com/ordermanagement/v1/orders/', $this->klarnaPaymentsApiClient->getKlarnaOrderManagementApiUrl());

        $config[KlarnaGatewayConfigInterface::CONFIG_API_ZONE] = KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA;
        $this->klarnaPaymentsApiClient->initialize($config);
        self::assertEquals('https://api-na.playground.klarna.com/payments/v1/', $this->klarnaPaymentsApiClient->getKlarnaPaymentsApiUrl());
        self::assertEquals('https://api-na.playground.klarna.com/ordermanagement/v1/orders/', $this->klarnaPaymentsApiClient->getKlarnaOrderManagementApiUrl());

        $config[KlarnaGatewayConfigInterface::CONFIG_API_ZONE] = KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE;
        $this->klarnaPaymentsApiClient->initialize($config);
        self::assertEquals('https://api.playground.klarna.com/payments/v1/', $this->klarnaPaymentsApiClient->getKlarnaPaymentsApiUrl());
        self::assertEquals('https://api.playground.klarna.com/ordermanagement/v1/orders/', $this->klarnaPaymentsApiClient->getKlarnaOrderManagementApiUrl());
    }
}
