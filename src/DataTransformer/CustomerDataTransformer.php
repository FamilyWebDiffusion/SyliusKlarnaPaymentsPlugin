<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Webmozart\Assert\Assert;

class CustomerDataTransformer implements DataTransformerInterface
{
    private bool $isBirthdaySendToKlarna;

    public function __construct(bool $isBirthdaySendToKlarna = true)
    {
        $this->isBirthdaySendToKlarna = $isBirthdaySendToKlarna;
    }

    public function __invoke(array $data, PaymentInterface $payment): array
    {
        Assert::notNull($payment->getOrder());
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $customer = $order->getCustomer();
        Assert::notNull($customer);

        $birthday = $customer->getBirthday();
        $customerArray = \array_filter(
            [
                'date_of_birth' => $this->isBirthdaySendToKlarna && $birthday !== null ? $birthday->format(KlarnaDataInterface::BIRTHDAY_FORMAT) : null,
                'gender' => $this->getGender($customer),
            ],
        );

        if ($customerArray !== []) {
            $data['customer'] = $customerArray;
        }

        return $data;
    }

    public function isAnonymous(): bool
    {
        return false;
    }

    private function getGender(CustomerInterface $customer): ?string
    {
        if ($customer->getGender() === CustomerInterface::FEMALE_GENDER) {
            return KlarnaDataInterface::GENDER_FEMALE;
        }

        if ($customer->getGender() === CustomerInterface::MALE_GENDER) {
            return KlarnaDataInterface::GENDER_MALE;
        }

        return null;
    }
}
