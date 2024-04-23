<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client\RetryableHttpClientFactory;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RetryableHttpClientFactorySpec extends ObjectBehavior
{
    public function let(
        HttpClientInterface $client
    ): void {
        $this->beConstructedWith(
            $client,
            3
        );
    }
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RetryableHttpClientFactory::class);
    }

    public function return_retryable_client(): void
    {
        $this->getRetryableClient()->shouldBeAnInstanceOf(RetryableHttpClient::class);
    }
}
