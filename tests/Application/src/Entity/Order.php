<?php

declare(strict_types=1);

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderTrait;
use Sylius\Component\Core\Model\Order as BaseOrder;

class Order extends BaseOrder implements OrderInterface
{
    use OrderTrait;
}
