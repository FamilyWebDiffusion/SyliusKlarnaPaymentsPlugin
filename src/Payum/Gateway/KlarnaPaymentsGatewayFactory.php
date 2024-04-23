<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action\AuthorizeByCallbackAction;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action\CancelAction;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action\RefundAction;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action\StatusAction;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class KlarnaPaymentsGatewayFactory extends GatewayFactory
{
    public const NAME = 'klarna_payments';

    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => self::NAME,
            'payum.factory_title' => 'Klarna Payments',
            'payum.action.authorize_callback' => new AuthorizeByCallbackAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.refund' => new RefundAction(),
        ]);

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX => true,
                KlarnaGatewayConfigInterface::CONFIG_API_USERNAME => '',
                KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD => '',
                KlarnaGatewayConfigInterface::CONFIG_API_ZONE => '',
                KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD => '',
            ];

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = [
                KlarnaGatewayConfigInterface::CONFIG_API_USERNAME,
                KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD,
                KlarnaGatewayConfigInterface::CONFIG_API_ZONE,
                KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD,
            ];

            $config['payum.http_client'] = '@FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClient';

            $config['payum.api'] = static function (ArrayObject $config): KlarnaPaymentsApiClientInterface {
                $config->validateNotEmpty($config['payum.required_options']);

                /** @var KlarnaPaymentsApiClientInterface $klarnaPaymentApiClient */
                $klarnaPaymentApiClient = $config['payum.http_client'];
                $klarnaPaymentApiClient->initialize(
                    [
                        KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX => $config[KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX],
                        KlarnaGatewayConfigInterface::CONFIG_API_USERNAME => $config[KlarnaGatewayConfigInterface::CONFIG_API_USERNAME],
                        KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD => $config[KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD],
                        KlarnaGatewayConfigInterface::CONFIG_API_ZONE => $config[KlarnaGatewayConfigInterface::CONFIG_API_ZONE],
                        KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD => $config[KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD],
                    ],
                );

                return $klarnaPaymentApiClient;
            };
        }
    }
}
