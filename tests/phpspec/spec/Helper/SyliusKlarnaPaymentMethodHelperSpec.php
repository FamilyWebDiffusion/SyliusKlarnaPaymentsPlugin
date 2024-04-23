<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Helper\SyliusKlarnaPaymentMethodHelper;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;

class SyliusKlarnaPaymentMethodHelperSpec extends ObjectBehavior
{
    function let(PaymentMethodRepositoryInterface $paymentMethodRepository) {
        $this->beConstructedWith($paymentMethodRepository);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(SyliusKlarnaPaymentMethodHelper::class);
    }

    function it_return_nothing_if_order_has_no_channel(
        OrderInterface $order,
    ): void
    {
        $this->getKlarnaMethods($order)->shouldReturn([]);
    }

    function it_returns_klarna_methods(
        OrderInterface $order,
        ChannelInterface $channel,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $paymentMethod1,
        PaymentMethodInterface $paymentMethod2,
        PaymentMethodInterface $paymentMethod3,
        ChannelInterface $otherChannel,
        GatewayConfigInterface $gatewayConfig1,
        GatewayConfigInterface $gatewayConfig2,
    ): void
    {
        $paymentMethod1->getChannels()->willReturn(new ArrayCollection([$channel->getWrappedObject()]));
        $paymentMethod2->getChannels()->willReturn(new ArrayCollection([$channel->getWrappedObject(), $otherChannel->getWrappedObject()]));
        $paymentMethod3->getChannels()->willReturn(new ArrayCollection([$otherChannel->getWrappedObject()]));
        $order->getChannel()->willReturn($channel->getWrappedObject());


        //it's klarna gateway
        $gatewayConfig1->getConfig()->willReturn(['factory_name' => 'klarna_payments', 'payment_method' => 'pay_later']);
        $gatewayConfig2->getConfig()->willReturn(['factory_name' => 'klarna_payments', 'payment_method' => 'pay_now']);

        $paymentMethod1->getGatewayConfig()->shouldBeCalled()->willReturn($gatewayConfig1);
        $paymentMethod2->getGatewayConfig()->shouldBeCalled()->willReturn($gatewayConfig2);
        $paymentMethod3->getGatewayConfig()->shouldNotBeCalled();

        $paymentMethod1->getCode()->willReturn('klarna_pay_later');
        $paymentMethod2->getCode()->willReturn('klarna_pay_now');
        $paymentMethod3->getCode()->willReturn('klarna_pay_overtime');

        $paymentMethods = new ArrayCollection([$paymentMethod3->getWrappedObject(), $paymentMethod2->getWrappedObject(), $paymentMethod1->getWrappedObject()]);
        $paymentMethodRepository->findBy(['enabled' => true])->willReturn($paymentMethods);

        $this->getKlarnaMethods($order)->shouldReturn(['klarna_pay_now' => 'pay_now', 'klarna_pay_later' => 'pay_later' ]);
    }

    function it_returns_only_klarna_methods(
        OrderInterface $order,
        ChannelInterface $channel,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $paymentMethod1,
        PaymentMethodInterface $paymentMethod2,
        PaymentMethodInterface $paymentMethod3,
        ChannelInterface $otherChannel,
        GatewayConfigInterface $gatewayConfig1,
        GatewayConfigInterface $gatewayConfig2,
    ): void
    {
        $paymentMethod1->getChannels()->willReturn(new ArrayCollection([$channel->getWrappedObject()]));
        $paymentMethod2->getChannels()->willReturn(new ArrayCollection([$channel->getWrappedObject(), $otherChannel->getWrappedObject()]));
        $paymentMethod3->getChannels()->willReturn(new ArrayCollection([$otherChannel->getWrappedObject()]));
        $paymentMethods = new ArrayCollection([$paymentMethod3->getWrappedObject(), $paymentMethod2->getWrappedObject(), $paymentMethod1->getWrappedObject()]);
        $order->getChannel()->willReturn($channel);
        $paymentMethodRepository->findBy(['enabled' => true])->willReturn($paymentMethods);


        $gatewayConfig1->getConfig()->willReturn(['factory_name' => 'klarna_payments', 'payment_method' => 'pay_later']);
        $gatewayConfig2->getConfig()->willReturn(['factory_name' => 'check']);

        $paymentMethod1->getGatewayConfig()->shouldBeCalled()->willReturn($gatewayConfig1);
        $paymentMethod2->getGatewayConfig()->shouldBeCalled()->willReturn($gatewayConfig2);
        $paymentMethod3->getGatewayConfig()->shouldNotBeCalled();

        $paymentMethod1->getCode()->willReturn('klarna_pay_later');
        $paymentMethod2->getCode()->willReturn('check');
        $paymentMethod3->getCode()->willReturn(null);

        $this->getKlarnaMethods($order)->shouldReturn(['klarna_pay_later' => 'pay_later']);
    }
}
