<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\Helper\EuAndAustraliaTaxDataHelper;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\Helper\UsTaxDataHelper;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class TaxDataTransformer implements DataTransformerInterface
{
    public function __invoke(array $data, PaymentInterface $payment): array
    {
        $order = $payment->getOrder();
        Assert::notNull($order);
        $billingAddress = $order->getBillingAddress();
        Assert::notNull($billingAddress);

        if ($billingAddress->getCountryCode() === 'US') {
            UsTaxDataHelper::addOrderItemTax($data, $payment);
        } else {
            EuAndAustraliaTaxDataHelper::addOrderItemTax($data, $payment);
        }

        return $data;
    }

    public function isAnonymous(): bool
    {
        return true;
    }
}
