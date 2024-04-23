<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client;

use Symfony\Component\HttpClient\Retry\GenericRetryStrategy;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RetryableHttpClientFactory
{
    private RetryableHttpClient $retryableHttpClient;

    public function __construct(HttpClientInterface $client, int $maxRetry = 3)
    {
        $this->retryableHttpClient = new RetryableHttpClient($client, new GenericRetryStrategy(), $maxRetry);
    }

    public function getRetryableClient(): RetryableHttpClient
    {
        return $this->retryableHttpClient;
    }
}
