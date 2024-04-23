<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;

interface KlarnaGatewayConfigInterface
{
    public const TRANSLATION_KEY = 'sylius.form.gateway_configuration.familywebdiffusion_klarna_payments.';

    public const CONFIG_FACTORY_NAME = 'factory_name';

    public const CONFIG_API_USERNAME = 'api_username';

    public const CONFIG_API_PASSWORD = 'api_password';

    public const CONFIG_API_SANDBOX = 'sandbox';

    public const CONFIG_API_ZONE = 'api_zone';

    public const CONFIG_API_PAYMENT_ENDPOINT = 'api_payment_endpoint';

    public const CONFIG_API_ORDERMANAGMENT_ENDPOINT = 'api_order_endpoint';

    public const CONFIG_CLIENT_RETRY = 'client_retry_limit';

    public const CONFIG_DISPLAY_BIRTHDAY = 'display_birthday';

    public const CONFIG_API_SERVER_PROD = 'servers_prod';

    public const CONFIG_API_SERVER_TEST = 'servers_test';

    public const CONFIG_API_SERVER_EUROPE = 'EU';

    public const CONFIG_API_SERVER_NORTH_AMERICA = 'NA';

    public const CONFIG_API_SERVER_OCEANIA = 'OC';

    public const CONFIG_API_PAYMENT_METHOD = 'payment_method';

    public const PAYMENT_METHOD_IDS = [
        self::TRANSLATION_KEY . KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_LATER => KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_LATER,
        self::TRANSLATION_KEY . KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_NOW => KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_NOW,
        self::TRANSLATION_KEY . KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_OVER_TIME => KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_OVER_TIME,
        self::TRANSLATION_KEY . KlarnaDataInterface::PAYMENT_METHOD_ID_DIRECT_BANK_TRANSFER => KlarnaDataInterface::PAYMENT_METHOD_ID_DIRECT_BANK_TRANSFER,
        self::TRANSLATION_KEY . KlarnaDataInterface::PAYMENT_METHOD_ID_DIRECT_DEBIT => KlarnaDataInterface::PAYMENT_METHOD_ID_DIRECT_DEBIT,
    ];

    public function isSandbox(): bool;

    public function getApiZone(): string;

    public function getApiUsername(): string;

    public function getApiPassword(): string;

    public function getKlarnaPaymentsApiUrl(): string;

    public function getKlarnaOrderManagementApiUrl(): string;

    public function getKlarnaPaymentMethod(): string;
}
