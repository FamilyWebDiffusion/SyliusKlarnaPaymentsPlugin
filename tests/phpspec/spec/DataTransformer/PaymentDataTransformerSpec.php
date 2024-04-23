<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\CustomerDataTransformer;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\PaymentDataTransformer;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\PaymentDataTransformerInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Registry\PrioritizedServiceRegistryInterface;


class PaymentDataTransformerSpec extends ObjectBehavior
{
    function let(
        PrioritizedServiceRegistryInterface $resolversRegistry,
        PaymentInterface $payment,
        OrderInterface $order,
        AddressInterface $billingAddress,
        CustomerInterface $customer
    ): void
    {
        $this->beConstructedWith($resolversRegistry);

        $order->getLocaleCode()->willReturn('de');
        $order->getBillingAddress()->willReturn($billingAddress);
        $order->getCustomer()->willReturn($customer);
        $billingAddress->getCountryCode()->willReturn('DE');
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(500);
        $payment->getCurrencyCode()->willReturn('EUR');
        $customer->getBirthday()->willReturn(new \DateTime('1980-01-10'));
        $customer->getGender()->willReturn('f');

    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(PaymentDataTransformer::class);
    }

    function it_implements_data_transformer_interface(): void
    {
        $this->shouldImplement(PaymentDataTransformerInterface::class);
    }

    function it_transforms_sylius_payment_into_minimal_klarna_payment(
        PrioritizedServiceRegistryInterface $resolversRegistry,
        PaymentInterface $payment
    ): void {

        $data = [
            'locale' => 'de-DE',
            'order_amount' => 500,
            'purchase_currency' => 'EUR',
            'purchase_country' => 'DE',
            'order_lines' => [],
            ];

        $resolversRegistry->all()->willReturn([]);

        $this->transform([],$payment)->shouldReturn($data);
    }

    function it_transforms_sylius_payment_into_klarna_payment_with_customer_info(
        PrioritizedServiceRegistryInterface $resolversRegistry,
        PaymentInterface $payment,
        CustomerDataTransformer $customerDataTransformer
    ): void {

        $data1 = [
            'locale' => 'de-DE',
            'order_amount' => 500,
            'purchase_currency' => 'EUR',
            'purchase_country' => 'DE',
            'order_lines' => [],
            ];

        $data2 = array_merge($data1, [
            'date_of_birth' => '1980-01-10',
            'gender' => KlarnaDataInterface::GENDER_FEMALE
            ]);

        $resolversRegistry->all()->willReturn([$customerDataTransformer]);
        $customerDataTransformer->__invoke($data1, $payment)->willReturn($data2);

        $this->transform([],$payment)->shouldReturn($data2);
    }

    function it_transforms_sylius_payment_into_klarna_payment_without_customer_info(
        PrioritizedServiceRegistryInterface $resolversRegistry,
        PaymentInterface $payment,
        CustomerDataTransformer $customerDataTransformer
    ): void {

        $data1 = [
            'locale' => 'de-DE',
            'order_amount' => 500,
            'purchase_currency' => 'EUR',
            'purchase_country' => 'DE',
            'order_lines' => [],
        ];

        $resolversRegistry->all()->willReturn([$customerDataTransformer]);
        $customerDataTransformer->__invoke($data1, $payment)->shouldNotBeCalled();
        $customerDataTransformer->isAnonymous()->willReturn(false);

        $this->transformAnonymized([],$payment)->shouldReturn($data1);
    }
}
