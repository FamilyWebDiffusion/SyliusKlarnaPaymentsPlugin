<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Exception;

final class KlarnaRequestException extends \Exception
{
    public function __construct(string $message, string $url)
    {
        parent::__construct(sprintf('[klarna] %s, API request to %s failed', $message, $url));
    }
}
