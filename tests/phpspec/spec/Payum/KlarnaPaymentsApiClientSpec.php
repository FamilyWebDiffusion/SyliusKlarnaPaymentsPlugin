<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client\HttpClientInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\PaymentDataTransformer;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\ShippingInfoDataTransformerInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaPaymentsSession;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Exception\KlarnaRequestException;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClient;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class KlarnaPaymentsApiClientSpec extends ObjectBehavior
{
    function let(
        HttpClientInterface                  $client,
        PaymentDataTransformer               $paymentDataTransformer,
        PaymentInterface                     $payment,
        OrderInterface                       $order,
        AddressInterface                     $billingAddress,
        ShippingInfoDataTransformerInterface $shippingInfoDataTransformer
    ): void
    {
        $config = [
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
        ];

        $this->beConstructedWith($client, $paymentDataTransformer, $shippingInfoDataTransformer, $config);

        $config = [
            KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX => true,
            KlarnaGatewayConfigInterface::CONFIG_API_ZONE => KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE,
            KlarnaGatewayConfigInterface::CONFIG_API_USERNAME => 'test',
            KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD => 'testpass',
            KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD => 'pay_later',
        ];

        $this->initialize($config);

        $order->getLocaleCode()->willReturn('de');
        $order->getBillingAddress()->willReturn($billingAddress);
        $order->getNumber()->willReturn('1234');
        $billingAddress->getCountryCode()->willReturn('DE');
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(500);
        $payment->getCurrencyCode()->willReturn('EUR');

        $paymentDataTransformer->transform([], $payment)->willReturn(
            [
                'locale' => 'de-DE',
                'order_amount' => 500,
                'purchase_currency' => 'EUR',
                'purchase_country' => 'DE',
                'order_lines' => [],
            ]
        );
        $paymentDataTransformer->transformAnonymized([], $payment)->willReturn(
            [
                'locale' => 'de-DE',
                'order_amount' => 500,
                'purchase_currency' => 'EUR',
                'purchase_country' => 'DE',
                'order_lines' => [],
            ]
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(KlarnaPaymentsApiClient::class);
    }

    function it_implements_klarna_payments_api_client_interface(): void
    {
        $this->shouldHaveType(KlarnaPaymentsApiClientInterface::class);
    }

    function it_should_return_klarna_test_api_url()
    {

        $this->getKlarnaPaymentsApiUrl()->shouldReturn('https://api.playground.klarna.com/payments/v1/');
        $this->getKlarnaOrderManagementApiUrl()->shouldReturn('https://api.playground.klarna.com/ordermanagement/v1/orders/');
    }

    function it_should_return_klarna_prod_api_url()
    {
        $config = [
            KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX => false,
            KlarnaGatewayConfigInterface::CONFIG_API_ZONE => KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA,
            KlarnaGatewayConfigInterface::CONFIG_API_USERNAME => 'test',
            KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD => 'testpass',
            KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD => 'pay_later',
        ];

        $this->initialize($config);
        $this->getKlarnaPaymentsApiUrl()->shouldReturn('https://api-na.klarna.com/payments/v1/');
        $this->getKlarnaOrderManagementApiUrl()->shouldReturn('https://api-na.klarna.com/ordermanagement/v1/orders/');
    }

    function it_creates_a_klarna_payment_session(
        PaymentInterface $payment,
        HttpClientInterface $client
    ): void {

        $client->request(
            'POST',
            'https://api.playground.klarna.com/payments/v1/sessions',
            [
            'auth_basic' => ['test', 'testpass'],
            'headers' => ['Content-Type' => 'application/json']
            ],
            [
            'locale' => 'de-DE',
            'order_amount' => 500,
            'purchase_currency' => 'EUR',
            'purchase_country' => 'DE',
            'order_lines' => []
            ]
        )->willReturn([
            'session_id' => 'c9a97141',
            'client_token' => 'token',
            'payment_method_categories' => [
                [
                    'identifier' => 'pay_later',
                    'name' => 'Rechnung.',
                ]
            ],
            'success' => true,
            'status_code' => 200
        ])->shouldBeCalled();

    $this->createAnonymousKlarnaPaymentSession($payment)->shouldReturnAnInstanceOf(KlarnaPaymentsSession::class);
    }

    function it_throws_error_if_klarna_api_could_not_create_session(
        PaymentInterface $payment,
        HttpClientInterface $client
    ): void {

        $client->request(
            'POST',
            'https://api.playground.klarna.com/payments/v1/sessions',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            [
                'locale' => 'de-DE',
                'order_amount' => 500,
                'purchase_currency' => 'EUR',
                'purchase_country' => 'DE',
                'order_lines' => []
            ]
        )->willReturn([
            'success' => false,
            'status_code' => 400
        ])->shouldBeCalled();

    $this->shouldThrow(KlarnaRequestException::class)->during('createAnonymousKlarnaPaymentSession', [$payment]);
    }

    function it_updates_a_klarna_payment_session(
        PaymentInterface $payment,
        HttpClientInterface $client
    ): void {

        $client->request(
            'POST',
            'https://api.playground.klarna.com/payments/v1/sessions/c9a97141',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            [
                'locale' => 'de-DE',
                'order_amount' => 500,
                'purchase_currency' => 'EUR',
                'purchase_country' => 'DE',
                'order_lines' => []
            ]
        )->willReturn([
            'success' => true,
            'status_code' => 204
        ])->shouldBeCalled();

        $this->updateAnonymousKlarnaPaymentSession($payment, 'c9a97141');
    }

    function it_throws_error_if_klarna_api_could_not_update_session(
        PaymentInterface $payment,
        HttpClientInterface $client
    ): void {

        $client->request(
            'POST',
            'https://api.playground.klarna.com/payments/v1/sessions/c9a97141',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            [
                'locale' => 'de-DE',
                'order_amount' => 500,
                'purchase_currency' => 'EUR',
                'purchase_country' => 'DE',
                'order_lines' => []
            ]
        )->willReturn([
            'success' => false,
            'status_code' => 400
        ])->shouldBeCalled();

        $this->shouldThrow(KlarnaRequestException::class)->during('updateAnonymousKlarnaPaymentSession', [$payment, 'c9a97141']);
    }

    function it_gets_a_klarna_payment_session(
        HttpClientInterface $client
    ): void {

        $client->request(
            'GET',
            'https://api.playground.klarna.com/payments/v1/sessions/c9a97141',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            []
        )->willReturn([
            'client_token' => 'eyJhbGciOiJS',
            'status' => 'incomplete',
            'success' => true,
            'status_code' => 200
        ])->shouldBeCalled();

        $this->getKlarnaPaymentSession('c9a97141')->shouldReturnAnInstanceOf(KlarnaPaymentsSession::class);
    }

    function it_throws_error_if_klarna_api_could_not_get_session(
        HttpClientInterface $client
    ): void {

        $client->request(
            'GET',
            'https://api.playground.klarna.com/payments/v1/sessions/c9a97141',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            []
        )->willReturn([
            'success' => false,
            'status_code' => 400
        ])->shouldBeCalled();

        $this->shouldThrow(KlarnaRequestException::class)->during('getKlarnaPaymentSession', ['c9a97141']);
    }

    function it_cancel_a_klarna_authorization(
        HttpClientInterface $client
    ): void {
        $client->request(
            'DELETE',
            'https://api.playground.klarna.com/payments/v1/authorizations/G6OzlHchzklBcd0',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            []
        )->willReturn([
            'success' => true,
            'status_code' => 204
        ])->shouldBeCalled();

        $this->cancelKlarnaAuthorization('G6OzlHchzklBcd0')
            ->shouldReturn(true);
    }

    function it_fail_to_cancel_a_klarna_authorization(
        HttpClientInterface $client
    ): void {
        $client->request(
            'DELETE',
            'https://api.playground.klarna.com/payments/v1/authorizations/G6OzlHchzklBcd0',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            []
        )->willReturn([
            'success' => false,
            'status_code' => 404
        ])->shouldBeCalled();

        $this->cancelKlarnaAuthorization('G6OzlHchzklBcd0')
            ->shouldReturn(false);
    }

    function it_creates_a_klarna_order(
        PaymentInterface $payment,
        HttpClientInterface $client
    ): void {

        $client->request(
            'POST',
            'https://api.playground.klarna.com/payments/v1/authorizations/G6OzlHchzklBcd0/order',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            [
                'locale' => 'de-DE',
                'order_amount' => 500,
                'purchase_currency' => 'EUR',
                'purchase_country' => 'DE',
                'order_lines' => [],
                'merchant_reference1' => '1234'
            ]
        )->willReturn([
            'fraud_status' => KlarnaDataInterface::FRAUD_STATUS_ACCEPTED,
            'success' => true,
            'status_code' => 200
        ])->shouldBeCalled();

        $this->createNewKlarnaOrder($payment, 'G6OzlHchzklBcd0')
            ->shouldReturn(
                [
                  'fraud_status' => 'ACCEPTED',
                  'success' => true,
                  'status_code' => 200,
                  'status' => 'authorized'
                ]
            );
    }

    function it_cancels_a_klarna_order(
        PaymentInterface $payment,
        HttpClientInterface $client
    ): void {

        $client->request(
            'POST',
            'https://api.playground.klarna.com/ordermanagement/v1/orders/STC99FKG/cancel',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            []
        )->willReturn([
            'success' => true,
            'status_code' => 200
        ])->shouldBeCalled();

        $payment->getDetails()->willReturn(['order_id' => 'STC99FKG']);
        $this->cancelKlarnaOrder($payment)
            ->shouldReturn(
                [
                    'success' => true,
                    'status_code' => 200,
                    'status' => 'cancel'
                ]
            );
    }

    function it_captures_a_klarna_order(
        PaymentInterface                     $payment,
        HttpClientInterface                  $client,
        ShippingInfoDataTransformerInterface $shippingInfoDataTransformer
    ): void {

        $client->request(
            'POST',
            'https://api.playground.klarna.com/ordermanagement/v1/orders/STC99FKG/captures',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            ['captured_amount' => 500]
        )->willReturn([
            'success' => true,
            'status_code' => 200
        ])->shouldBeCalled();

        $payment->getDetails()->willReturn(['order_id' => 'STC99FKG']);

        $shippingInfoDataTransformer->transform($payment)->shouldBeCalled();

        $this->captureAllApprovedKlarnaOrder($payment)
            ->shouldReturn(
                [
                    'success' => true,
                    'status_code' => 200,
                    'status' => 'captured'
                ]
            );
    }

    function it_refunds_a_klarna_order(
        PaymentInterface $payment,
        HttpClientInterface $client
    ): void {

        $client->request(
            'POST',
            'https://api.playground.klarna.com/ordermanagement/v1/orders/STC99FKG/refunds',
            [
                'auth_basic' => ['test', 'testpass'],
                'headers' => ['Content-Type' => 'application/json']
            ],
            ['refunded_amount' => 500]
        )->willReturn([
            'success' => true,
            'status_code' => 200
        ])->shouldBeCalled();

        $payment->getDetails()->willReturn(['order_id' => 'STC99FKG']);
        $this->refundAllKlarnaOrder($payment)
            ->shouldReturn(
                [
                    'success' => true,
                    'status_code' => 200,
                    'status' => 'refunded'
                ]
            );
    }
}
