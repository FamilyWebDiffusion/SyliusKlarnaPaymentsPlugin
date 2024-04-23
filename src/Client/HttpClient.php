<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpClient implements HttpClientInterface
{
    private RetryableHttpClientFactory $retryableHttpClientFactory;

    private LoggerInterface $logger;

    public function __construct(RetryableHttpClientFactory $retryableHttpClientFactory, LoggerInterface $logger)
    {
        $this->retryableHttpClientFactory = $retryableHttpClientFactory;

        $this->logger = $logger;
    }

    /**
     * @param array<string, array|string> $options
     * @param array<string> $data
     *
     * @return array<string, array|string|bool|int>
     */
    public function request(string $method, string $fullUrl, array $options = [], array $data = []): array
    {
        if ($data !== []) {
            $options['json'] = $data;
        }

        try {
            $response = $this->retryableHttpClientFactory->getRetryableClient()->request($method, $fullUrl, $options);

            if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
                $info = $response->getInfo();

                $this->logger->error(
                    sprintf(
                        'Error %d %s Request to %s failed for reason %s',
                        $response->getStatusCode(),
                        $method,
                        $fullUrl,
                        $info['error'] ?? '',
                    ),
                );

                $errorContent = $this->decodeResponse($response);
                if (isset($errorContent['error_messages'])) {
                    if (is_string($errorContent['error_messages'])) {
                        $message = $errorContent['error_messages'];
                    } else {
                        $message = $errorContent['error_messages'][0];
                    }

                    $this->logger->error(\sprintf('Error Message : %s', $message));
                }

                return ['success' => false, 'status_code' => $response->getStatusCode(), 'status' => ''];
            }

            $content = [];
            if ($response->getStatusCode() === 200) {
                $content = $this->decodeResponse($response);
            }
            $content['success'] = true;
            $content['status_code'] = $response->getStatusCode();

            return $content;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(sprintf(
                'API Error %s Request to %s failed for reason %s',
                $method,
                $fullUrl,
                $e->getMessage(),
            ));

            return ['success' => false, 'status_code' => 0];
        }
    }

    /**
     * @return array<array<string>|string>
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        try {
            $array = $response->toArray(false);
        } catch (ExceptionInterface $e) {
            return [];
        }

        return $array;
    }
}
