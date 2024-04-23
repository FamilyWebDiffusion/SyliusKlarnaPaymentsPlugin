<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

interface KlarnaDataInterface
{
    public const GENDER_FEMALE = 'female';

    public const GENDER_MALE = 'male';

    public const BIRTHDAY_FORMAT = 'Y-m-d';

    public const ORDER_LINE_TYPE_PHYSICAL = 'physical';

    public const ORDER_LINE_TYPE_DISCOUNT = 'discount';

    public const ORDER_LINE_TYPE_SHIPPING_FEE = 'shipping_fee';

    public const ORDER_LINE_TYPE_SALES_TAX = 'sales_tax';

    public const ORDER_LINE_TYPE_DIGITAL = 'digital';

    public const ORDER_LINE_TYPE_GIFT_CARD = 'gift_card';

    public const ORDER_LINE_TYPE_STORE_CREDIT = 'store_credit';

    public const ORDER_LINE_TYPE_SURCHARGE = 'surcharge';

    public const PAYMENT_METHOD_ID_PAY_LATER = 'pay_later';

    public const PAYMENT_METHOD_ID_PAY_NOW = 'pay_now';

    public const PAYMENT_METHOD_ID_PAY_OVER_TIME = 'pay_over_time';

    public const PAYMENT_METHOD_ID_DIRECT_BANK_TRANSFER = 'direct_bank_transfer';

    public const PAYMENT_METHOD_ID_DIRECT_DEBIT = 'direct_debit';

    public const FRAUD_STATUS_ACCEPTED = 'ACCEPTED';

    public const FRAUD_STATUS_PENDING = 'PENDING';
}
