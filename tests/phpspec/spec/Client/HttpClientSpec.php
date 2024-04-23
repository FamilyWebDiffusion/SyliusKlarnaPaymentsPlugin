<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client\HttpClient;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client\HttpClientInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\RetryableHttpClient;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client\RetryableHttpClientFactory;

class HttpClientSpec extends ObjectBehavior
{
    function let(
        RetryableHttpClientFactory $clientFactory,
        LoggerInterface $logger
    ): void {
        $this->beConstructedWith(
            $clientFactory,
            $logger
        );
    }
    function it_is_initializable(): void
    {
        $this->shouldHaveType(HttpClient::class);
    }

    function it_implements_klarna_payments_client_interface(): void
    {
        $this->shouldHaveType(HttpClientInterface::class);
    }

    function it_returns_succes_status_for_successful_request(
        RetryableHttpClientFactory $clientFactory,
        RetryableHttpClient $client,
        ResponseInterface $response
    ): void {
        $clientFactory->getRetryableClient()->willReturn($client);

        $response->getStatusCode()->willReturn(200);
        $response->toArray(false)->willReturn([
            'session_id' => 'c9a97141',
            'client_token' => 'token',
            'payment_method_categories' => [
                [
                    'identifier' => 'pay_later',
                    'name' => 'Rechnung.'
                ]
            ]
        ]);

        $client->request(
            'POST',
            'https://api.playground.klarna.com/payments/v1/sessions',
            [
                'auth_basic' => ['username', 'password'],
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    "purchase_country" => "DE",
                    "purchase_currency" => "EUR",
                    "locale" => "de-DE",
                    "order_amount" => 500,
                    "order_lines" => [
                        [
                            "name" => "Battery Power Pack",
                            "quantity" => 1,
                            "unit_price" => 500,
                            "total_amount" => 500
                        ]
                    ]
                ]
            ],
        )->willReturn($response);


        $this->request(
            'POST',
            'https://api.playground.klarna.com/payments/v1/sessions',
            [
                'auth_basic' => ['username', 'password'],
                'headers' => ['Content-Type' => 'application/json'],

            ],
            [
                "purchase_country" => "DE",
                "purchase_currency" => "EUR",
                "locale" => "de-DE",
                "order_amount" => 500,
                "order_lines" => [
                    [
                    "name" => "Battery Power Pack",
                    "quantity" => 1,
                    "unit_price" => 500,
                    "total_amount" => 500
                    ]
                ]
            ]
        )->shouldReturn([
            "session_id" => "c9a97141",
            "client_token" => "token",
            "payment_method_categories" => [
                [
                    "identifier" => "pay_later",
                    "name" => "Rechnung.",
                ]
            ],
            "success" => true,
            "status_code" => 200
        ]);
    }

    function it_log_error_message_from_failed_request(
        RetryableHttpClientFactory $clientFactory,
        RetryableHttpClient $client,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        $clientFactory->getRetryableClient()->willReturn($client);
        $client->request(
            'POST',
            'https://api.playground.klarna.com/payments/v1/sessions',
            [
                'auth_basic' => ['username', 'password'],
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    "purchase_country" => "DE",
                    "purchase_currency" => "EUR",
                    "locale" => "de-DE",
                    "order_amount" => 600,
                    "order_lines" => [
                        [
                            "name" => "Battery Power Pack",
                            "quantity" => 1,
                            "unit_price" => 500,
                            "total_amount" => 500
                        ]
                    ]
                ]
            ],
        )->willReturn($response);
        $response->getStatusCode()->willReturn(400);
        $response->getInfo()->willReturn(['error' => 'Bad Request']);
        $response->toArray(false)->willReturn([
            'error_code' =>  'BAD_VALUE',
            'error_messages' => ['Bad value: order_amount']
        ]);

        $logger
            ->error('Error 400 POST Request to https://api.playground.klarna.com/payments/v1/sessions failed for reason Bad Request')
            ->shouldBeCalled();
        $logger
            ->error('Error Message : Bad value: order_amount')
            ->shouldBeCalled();

        $this->request(
            'POST',
            'https://api.playground.klarna.com/payments/v1/sessions',
            [
                'auth_basic' => ['username', 'password'],
                'headers' => ['Content-Type' => 'application/json'],

            ],
            [
                "purchase_country" => "DE",
                "purchase_currency" => "EUR",
                "locale" => "de-DE",
                "order_amount" => 600,
                "order_lines" => [
                    [
                        "name" => "Battery Power Pack",
                        "quantity" => 1,
                        "unit_price" => 500,
                        "total_amount" => 500
                    ]
                ]
            ]
        )->shouldReturn([
            'success' => false,
            'status_code' => 400,
            'status' => ""
        ]);
    }
}

