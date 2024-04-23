<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Registry\PrioritizedServiceRegistryInterface;
use Webmozart\Assert\Assert;

class PaymentDataTransformer implements PaymentDataTransformerInterface
{
    private PrioritizedServiceRegistryInterface $registry;

    public function __construct(PrioritizedServiceRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<string, string|array>
     */
    public function transform(array $data, PaymentInterface $payment): array
    {
        $data = \array_merge($data, $this->transformPaymentData($payment));

        /** @var DataTransformerInterface $transformer */
        foreach ($this->registry->all() as $transformer) {
            $data = $transformer($data, $payment);
        }

        return $data;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<string, string|array>
     */
    public function transformAnonymized(array $data, PaymentInterface $payment): array
    {
        $data = \array_merge($data, $this->transformPaymentData($payment));

        foreach ($this->registry->all() as $transformer) {
            if ($transformer->isAnonymous()) {
                $data = $transformer($data, $payment);
            }
        }

        return $data;
    }

    /**
     * @return array<string, array|int|string|null>
     */
    private function transformPaymentData(PaymentInterface $payment): array
    {
        $order = $payment->getOrder();
        Assert::notNull($order);
        $billingAddress = $order->getBillingAddress();
        Assert::notNull($billingAddress);

        $data = [];

        // Klarna use RFC 1766 and Sylius ISO 15897,
        // apparently major difference is changing _ to -
        // However, second term should be equal to country code, anyway Klarna fallback is english
        $data['locale'] = $this->normaliseKlarnaLocale($order->getLocaleCode(), $billingAddress->getCountryCode());
        $data['order_amount'] = $payment->getAmount();
        $data['purchase_currency'] = $payment->getCurrencyCode();
        $data['purchase_country'] = $billingAddress->getCountryCode();
        $data['order_lines'] = [];

        return $data;
    }

    private function normaliseKlarnaLocale(?string $syliusLocale, ?string $syliusCountryCode): string
    {
        if ($syliusLocale === null || $syliusCountryCode === null) {
            return 'en-US';
        }
        [$language] = \explode('_', $syliusLocale);

        return \sprintf('%s-%s', $language, $syliusCountryCode);
    }
}
