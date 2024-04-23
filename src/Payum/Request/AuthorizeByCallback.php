<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Request;

use Payum\Core\Request\Generic;

class AuthorizeByCallback extends Generic
{
    private array $response = [];

    public function getResponse(): array
    {
        return $this->response;
    }

    public function setResponse(array $response): void
    {
        $this->response = $response;
    }
}
