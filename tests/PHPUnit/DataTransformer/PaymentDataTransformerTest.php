<?php

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\PHPUnit\DataTransformer;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\PaymentDataTransformer;
use Sylius\Component\Core\Model\Address;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ShopUser;
use Sylius\Component\Currency\Model\Currency;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Locale\Model\Locale;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaymentDataTransformerTest extends WebTestCase
{
    private ?PaymentDataTransformer $paymentDataTransformer;


    public function setUp():void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->paymentDataTransformer = $container
            ->get('familywebdiffusion_sylius_klarna_payments_plugin.payment_data_builder');
    }

    public function testMinimalKlarnaFormatedPayment()
    {
        $payment = $this->generatePayment();

        $expected = $this->generateExpectedData();
        $data = $this->paymentDataTransformer->transform([], $payment);
        self::assertEquals($expected, $data);
    }

    public function testAnonymizedFormated()
    {
        $payment = $this->generatePayment();
        $data = $this->paymentDataTransformer->transformAnonymized([], $payment);
        $expected = $this->generateExpectedAnonymizedData();

        self::assertEquals($expected, $data);
    }

    public function testKlarnaFormatedPaymentWithCustomerInformation()
    {
        $payment = $this->generatePayment();
        $payment->getOrder()->getCustomer()->setGender(CustomerInterface::MALE_GENDER);
        $payment->getOrder()->getCustomer()->setBirthday(new \DateTime('1970-01-01'));

        $data = $this->paymentDataTransformer->transform([], $payment);

        $expected = $this->generateExpectedData();
        $expected['customer']  = [
                                'gender' => 'male',
                                'date_of_birth' => '1970-01-01'
                                ];

        self::assertEquals($expected, $data);
    }

    private function generateExpectedData(): array
    {
        return [
            'locale' => 'en-US',
            'order_amount' => 0,
            'purchase_currency' => 'USD',
            'purchase_country' => 'US',
            'billing_address' => [
                'city' => 'Fullerton',
                'country' => 'US',
                'family_name' => 'William',
                'given_name' => 'Young',
                'phone' => '+19096419882',
                'postal_code' => '93632',
                'region' => 'CA',
                'street_address' => '3687  Paradise Lane',
                'email' => 'william.young@sylius.com',
            ],
            'shipping_address' => [
                'city' => 'WINTER PARK',
                'country' => 'US',
                'family_name' => 'Lara S',
                'given_name' => 'Torres',
                'phone' => '+13212797936',
                'postal_code' => '32792',
                'region' => 'FL',
                'street_address' => '1148  Bird Street',
                'email' => 'william.young@sylius.com',
            ],
            'order_lines' => [
                [
                    'type' => 'sales_tax',
                    'name' => 'Tax',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_amount' => 0,
                ]
            ],
            'order_tax_amount' => 0,
        ];
    }

    private function generateExpectedAnonymizedData(): array
    {
        return [
            'locale' => 'en-US',
            'order_amount' => 0,
            'purchase_currency' => 'USD',
            'purchase_country' => 'US',
            'order_lines' => [
                [
                    'type' => 'sales_tax',
                    'name' => 'Tax',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_amount' => 0,
                ]
            ],
            'order_tax_amount' => 0,
        ];
    }


    private function generatePayment() : PaymentInterface
    {

        $order = new Order();
        $channel = new Channel();
        $channel->setEnabled(true);

        $locale = new Locale();
        $locale->setCode('en_US');
        $channel->setDefaultLocale($locale);
        $channel->addLocale($locale);
        $currency = new Currency();
        $currency->setCode('USD');
        $channel->setBaseCurrency($currency);
        $channel->addCurrency($currency);
        $order->setChannel($channel);
        $order->setLocaleCode('en_US');
        $order->setCurrencyCode($channel->getBaseCurrency()->getCode());
        $user = new ShopUser();
        $customer = new Customer();

        $customer->setFirstName('William');
        $customer->setLastName('Young');
        $customer->setEmail('william.young@sylius.com');
        $user->setUsername('william.young@sylius.com');
        $user->addRole('ROLE_USER');
        $customer->setUser($user);

        $billingAddress = new Address();
        $billingAddress->setFirstName('William');
        $billingAddress->setLastName('Young');
        $billingAddress->setPhoneNumber('909-641-9882');
        $billingAddress->setCompany('Acme');
        $billingAddress->setStreet('3687  Paradise Lane');
        $billingAddress->setCity('Fullerton');
        $billingAddress->setPostcode('93632');
        $billingAddress->setCountryCode('US');
        $billingAddress->setProvinceCode('CA');

        $shippingAddress = new Address();
        $shippingAddress->setFirstName('Lara S');
        $shippingAddress->setLastName('Torres');
        $shippingAddress->setPhoneNumber('321-279-7936');
        $shippingAddress->setStreet('1148  Bird Street');
        $shippingAddress->setCity('WINTER PARK');
        $shippingAddress->setPostcode('32792');
        $shippingAddress->setCountryCode('US');
        $shippingAddress->setProvinceCode('FL');

        $customer->setDefaultAddress($billingAddress);
        $order->setCustomer($customer);
        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($shippingAddress);
        $order->setCurrencyCode($currency->getCode());
        /** @var PaymentInterface $payment */
        $payment = new Payment();
        $payment->setOrder($order);
        $payment->setCurrencyCode($currency->getCode());

        return $payment;
    }

}
