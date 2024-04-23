<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway;

use Payum\Core\Bridge\Spl\ArrayObject;

final class KlarnaPaymentGatewayConfig implements KlarnaGatewayConfigInterface
{
    private ArrayObject $config;

    public function __construct(ArrayObject $config)
    {
        $config->validatedKeysSet([
            KlarnaGatewayConfigInterface::CONFIG_API_SERVER_PROD,
            KlarnaGatewayConfigInterface::CONFIG_API_SERVER_TEST,
            KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_ENDPOINT,
            KlarnaGatewayConfigInterface::CONFIG_API_ORDERMANAGMENT_ENDPOINT,
            KlarnaGatewayConfigInterface::CONFIG_API_USERNAME,
            KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD,
            KlarnaGatewayConfigInterface::CONFIG_API_ZONE,
            KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD,
        ]);

        $config[KlarnaGatewayConfigInterface::CONFIG_API_SERVER_PROD] = $this->trimStringArray($config[KlarnaGatewayConfigInterface::CONFIG_API_SERVER_PROD]);
        $config[KlarnaGatewayConfigInterface::CONFIG_API_SERVER_TEST] = $this->trimStringArray($config[KlarnaGatewayConfigInterface::CONFIG_API_SERVER_TEST]);
        $config[KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_ENDPOINT] = $this->trimString($config[KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_ENDPOINT]);
        $config[KlarnaGatewayConfigInterface::CONFIG_API_ORDERMANAGMENT_ENDPOINT] = $this->trimString($config[KlarnaGatewayConfigInterface::CONFIG_API_ORDERMANAGMENT_ENDPOINT]);

        $this->config = $config;
    }

    public function isSandbox(): bool
    {
        return $this->config[KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX];
    }

    public function getApiZone(): string
    {
        return $this->config[KlarnaGatewayConfigInterface::CONFIG_API_ZONE];
    }

    public function getApiUsername(): string
    {
        return $this->config[KlarnaGatewayConfigInterface::CONFIG_API_USERNAME];
    }

    public function getApiPassword(): string
    {
        return $this->config[KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD];
    }

    public function getKlarnaPaymentMethod(): string
    {
        return $this->config[KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD];
    }

    public function getKlarnaPaymentsApiUrl(): string
    {
        $serverURL = $this->getKlarnaApiUrl();

        return $serverURL . '/' . $this->config[KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_ENDPOINT] . '/';
    }

    public function getKlarnaOrderManagementApiUrl(): string
    {
        $serverURL = $this->getKlarnaApiUrl();

        return $serverURL . '/' . $this->config[KlarnaGatewayConfigInterface::CONFIG_API_ORDERMANAGMENT_ENDPOINT] . '/';
    }

    private function getKlarnaApiUrl(): string
    {
        $serverURL = '';
        $serverURLs = $this->isSandbox() ?
            $this->config[KlarnaGatewayConfigInterface::CONFIG_API_SERVER_TEST] :
            $this->config[KlarnaGatewayConfigInterface::CONFIG_API_SERVER_PROD];
        switch ($this->getApiZone()) {
            case KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE:
                $serverURL = $serverURLs[KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE];

                break;
            case KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA:
                $serverURL = $serverURLs[KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA];

                break;
            case KlarnaGatewayConfigInterface::CONFIG_API_SERVER_OCEANIA:
                $serverURL = $serverURLs[KlarnaGatewayConfigInterface::CONFIG_API_SERVER_OCEANIA];

                break;
        }

        return $serverURL;
    }

    /**
     * @param array<int, string> $arrayOfString
     *
     * @return array<int, string>
     */
    private function trimStringArray(array $arrayOfString): array
    {
        foreach ($arrayOfString as $id => $string) {
            $arrayOfString[$id] = $this->trimString($string);
        }

        return $arrayOfString;
    }

    private function trimString(string $string): string
    {
        return \trim($string, " \n\r\t\v\0/");
    }
}
