<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaPaymentsGatewayFactory;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;

class PayumTokenHelper
{
    private Payum $payum;

    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }

    public function createAuthorizeCallbackToken(OrderInterface $order, ?string $gatewayName): TokenInterface
    {
        if ($gatewayName === null) {
            $gatewayName = KlarnaPaymentsGatewayFactory::NAME;
        }
        $tokenFactory = $this->payum->getTokenFactory();
        $token = $tokenFactory->createToken(
            $gatewayName,
            $order,
            'familywebdiffusion_sylius_shop_payum_authorize_callback',
        );

        return $token;
    }
}
