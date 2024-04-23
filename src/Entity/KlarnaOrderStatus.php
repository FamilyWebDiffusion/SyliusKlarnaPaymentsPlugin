<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

interface KlarnaOrderStatus
{
    public const STATUS_AUTHORIZED = 'authorized';

    public const STATUS_CANCEL = 'cancel';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CAPTURED = 'captured';

    public const STATUS_REFUNDED = 'refunded';
}
