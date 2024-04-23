<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Webmozart\Assert\Assert;

class AddressesDataTransformer implements DataTransformerInterface
{
    private PhoneNumberUtil $phoneNumberUtil;

    public function __construct()
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * @param array<string, array|int|string|null> $data
     *
     * @return array<string,  array|int|string|null>
     */
    public function __invoke(array $data, PaymentInterface $payment): array
    {
        Assert::notNull($payment->getOrder());
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $customer = $order->getCustomer();
        Assert::notNull($customer);

        if ($order->getBillingAddress() === null) {
            return $data;
        }

        $data['billing_address'] = $this->getAddressData($order->getBillingAddress(), $customer);
        $data['shipping_address'] = $this->getAddressData($order->getShippingAddress(), $customer);

        return $data;
    }

    /**
     * @return array<string,string|null>
     */
    private function getAddressData(?AddressInterface $address, CustomerInterface $customer): array
    {
        if ($address === null) {
            return [];
        }

        $data = \array_filter(
            [
            'city' => $address->getCity(),
            'country' => $address->getCountryCode(),
            'family_name' => $address->getFirstName(),
            'given_name' => $address->getLastName(),
            'phone' => $this->formatPhoneNumber($address->getPhoneNumber(), $address->getCountryCode()),
            'postal_code' => $address->getPostcode(),
            'region' => $address->getProvinceCode(),
            'street_address' => $address->getStreet(),
            'email' => $customer->getEmail(),
            ],
        );

        if ($address->getProvinceCode() !== null) {
            $data['region'] = $address->getProvinceCode();
        }

        return $data;
    }

    public function formatPhoneNumber(?string $phoneNumber, ?string $countryCode): ?string
    {
        if ($phoneNumber === null || $phoneNumber === '' || $countryCode === null || $countryCode === '') {
            return null;
        }

        try {
            $phoneNumberObject = $this->phoneNumberUtil->parse($phoneNumber, $countryCode);
        } catch (NumberParseException $e) {
            return null;
        }
        $phoneNumberObject = $this->phoneNumberUtil->parse($phoneNumber, $countryCode);
        if (!$this->phoneNumberUtil->isValidNumber($phoneNumberObject)) {
            return null;
        }

        return $this->phoneNumberUtil->format($phoneNumberObject, PhoneNumberFormat::E164);
    }

    public function isAnonymous(): bool
    {
        return false;
    }
}
