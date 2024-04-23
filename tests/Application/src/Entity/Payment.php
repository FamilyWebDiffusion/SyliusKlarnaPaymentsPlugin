<?php

declare(strict_types=1);

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\PaymentInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\PaymentTrait;
use Sylius\Component\Core\Model\Payment as BasePayment;

class Payment extends BasePayment implements PaymentInterface
{
    use PaymentTrait;
}
