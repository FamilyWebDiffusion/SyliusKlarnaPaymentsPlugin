<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\PaymentDataTransformer;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClient;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaPaymentsSession;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\PayumTokenHelper;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Resolver\KlarnaPaymentMethodResolverDecorator;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaPaymentsGatewayFactory;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;

class KlarnaPaymentMethodResolverDecoratorSpec extends ObjectBehavior
{
    public function let(
        PaymentMethodsResolverInterface $paymentMethodsResolver,
        ContainerInterface $container,
        PayumTokenHelper $payumTokenHelper,
    ): void
    {
        $this->beConstructedWith(
            $paymentMethodsResolver,
            $container,
            $payumTokenHelper
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(KlarnaPaymentMethodResolverDecorator::class);
    }

    public function it_implements_payment_resolver_interface(): void
    {
        $this->shouldHaveType(PaymentMethodsResolverInterface::class);
    }

    public function it_return_valid_klarna_payment_method_among_others(
        ContainerInterface $container,
        EntityManagerInterface $entityManager,
        PaymentInterface $payment,
        OrderInterface $order,
        PaymentMethodsResolverInterface $paymentMethodsResolver,
        PaymentDataTransformer $paymentDataTransformer,
        KlarnaPaymentsApiClientInterface $klarnaPaymentsApiClient,
        KlarnaGatewayConfigInterface $klarnaApiConfigInterface,
        KlarnaPaymentsSession $klarnaPaymentsSession,

        PaymentMethodInterface $cashPaymentMethod,
        GatewayConfigInterface $cashGatewayConfig,

        PaymentMethodInterface $klarnaPaymentMethod,
        GatewayConfigInterface $klarnaGatewayConfig,
        PayumTokenHelper $payumTokenHelper,
        TokenInterface $token,
    ): void {
        $container->get('doctrine.orm.default_entity_manager')->willReturn($entityManager);

        $paymentDataTransformer->transform([], $payment)->willReturn([]);
        $container->get(KlarnaPaymentsApiClient::class)->willReturn($klarnaPaymentsApiClient);
        $payment->getOrder()->willReturn($order);

        $klarnaApiConfigInterface->getApiUsername()->willReturn('steph');
        $klarnaApiConfigInterface->getKlarnaPaymentMethod()->willReturn(KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_LATER);
        $klarnaPaymentsApiClient->createAnonymousKlarnaPaymentSession($payment)->willReturn($klarnaPaymentsSession);
        $klarnaPaymentsApiClient->initialize( [KlarnaGatewayConfigInterface::CONFIG_FACTORY_NAME => KlarnaPaymentsGatewayFactory::NAME])->shouldBeCalled();
        $klarnaPaymentsSession->setPaymentMethodCategories([['identifier' => KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_LATER]])->shouldBeCalled();
        $klarnaPaymentsSession->isCreated()->willReturn(true);
        $klarnaPaymentsSession->getPaymentMethodCategoriesIdentifiers()->willReturn([KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_LATER]);
        $klarnaPaymentsSession->getPaymentMethodCategories()->willReturn([['identifier' => KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_LATER]]);
        $klarnaPaymentsSession->getSessionId()->willReturn('abcd');
        $klarnaPaymentsSession->getAuthorizationCallbackUrl()->willReturn(null);

        $cashPaymentMethod->getGatewayConfig()->willReturn($cashGatewayConfig);
        $cashGatewayConfig->getConfig()->willReturn([]);

        $klarnaPaymentMethod->getId()->willReturn(1);
        $klarnaPaymentMethod->getGatewayConfig()->willReturn($klarnaGatewayConfig);
        $klarnaGatewayConfig->getGatewayName()->willReturn(KlarnaPaymentsGatewayFactory::NAME);
        $klarnaGatewayConfig->getConfig()->willReturn([KlarnaGatewayConfigInterface::CONFIG_FACTORY_NAME => KlarnaPaymentsGatewayFactory::NAME]);

        $payumTokenHelper->createAuthorizeCallbackToken($order,KlarnaPaymentsGatewayFactory::NAME)->willReturn($token);
        $token->getTargetUrl()->willReturn("http://my_site/klarna_authorize");

        $paymentMethodsResolver->getSupportedMethods($payment)->willReturn([$cashPaymentMethod, $klarnaPaymentMethod]);

        $klarnaPaymentsApiClient->getKlarnaPaymentGatewayConfig()->willReturn($klarnaApiConfigInterface);

        $klarnaPaymentsApiClient->updateReturnUrlOnKlarnaPaymentSession('http://my_site/klarna_authorize', 'abcd')->shouldBeCalled();

        $this->getSupportedMethods($payment)->shouldReturn([
                $cashPaymentMethod,
                $klarnaPaymentMethod
            ]);
    }

    public function it_remove_invalid_klarna_payment_method_among_others(
        ContainerInterface $container,
        EntityManagerInterface $entityManager,
        PaymentInterface $payment,
        OrderInterface $order,
        PaymentMethodsResolverInterface $paymentMethodsResolver,
        KlarnaPaymentsApiClientInterface $klarnaPaymentsApiClient,
        KlarnaGatewayConfigInterface $klarnaApiConfigInterface,
        KlarnaPaymentsSession $klarnaPaymentsSession,

        PaymentMethodInterface $cashPaymentMethod,
        GatewayConfigInterface $cashGatewayConfig,

        PaymentMethodInterface $klarnaPaymentMethod,
        GatewayConfigInterface $klarnaGatewayConfig

    ): void {
        $container->get('doctrine.orm.default_entity_manager')->willReturn($entityManager);
        $container->get(KlarnaPaymentsApiClient::class)->willReturn($klarnaPaymentsApiClient);
        $payment->getOrder()->willReturn($order);

        $klarnaApiConfigInterface->getApiUsername()->willReturn('steph');
        $klarnaApiConfigInterface->getKlarnaPaymentMethod()->willReturn(KlarnaDataInterface::PAYMENT_METHOD_ID_PAY_LATER);
        $klarnaPaymentsApiClient->createAnonymousKlarnaPaymentSession($payment)->willReturn($klarnaPaymentsSession);
        $klarnaPaymentsApiClient->initialize( [KlarnaGatewayConfigInterface::CONFIG_FACTORY_NAME => KlarnaPaymentsGatewayFactory::NAME])->shouldBeCalled();
        $klarnaPaymentsSession->isCreated()->willReturn(true);
        $klarnaPaymentsSession->getPaymentMethodCategoriesIdentifiers()->willReturn([KlarnaDataInterface::PAYMENT_METHOD_ID_DIRECT_DEBIT]);
        $klarnaPaymentsSession->getPaymentMethodCategories()->willReturn([['identifier' => KlarnaDataInterface::PAYMENT_METHOD_ID_DIRECT_DEBIT]]);
        $klarnaPaymentsSession->getSessionId()->willReturn('abcd');
        $klarnaPaymentsSession->getAuthorizationCallbackUrl()->willReturn(null);

        $cashPaymentMethod->getGatewayConfig()->willReturn($cashGatewayConfig);
        $cashGatewayConfig->getConfig()->willReturn([]);

        $klarnaPaymentMethod->getId()->willReturn(1);
        $klarnaPaymentMethod->getGatewayConfig()->willReturn($klarnaGatewayConfig);
        $klarnaGatewayConfig->getConfig()->willReturn([KlarnaGatewayConfigInterface::CONFIG_FACTORY_NAME => KlarnaPaymentsGatewayFactory::NAME]);

        $paymentMethodsResolver->getSupportedMethods($payment)->willReturn([$cashPaymentMethod, $klarnaPaymentMethod]);

        $klarnaPaymentsApiClient->getKlarnaPaymentGatewayConfig()->willReturn($klarnaApiConfigInterface);

        $this->getSupportedMethods($payment)->shouldReturn(
            [$cashPaymentMethod]
        );
    }


}
