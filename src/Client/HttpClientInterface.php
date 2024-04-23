<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client;

interface HttpClientInterface
{
    /**
     * @param array<string, array|string> $options
     * @param array<string> $data
     *
     * @return array<string, array|string|bool|int>
     */
    public function request(string $method, string $fullUrl, array $options = [], array $data = []): ?array;
}
