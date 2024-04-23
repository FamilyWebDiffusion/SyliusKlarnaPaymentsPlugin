<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaPaymentsGatewayFactory;
use Payum\Core\GatewayFactory;
use PhpSpec\ObjectBehavior;

class KlarnaPaymentsGatewayFactorySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(KlarnaPaymentsGatewayFactory::class);
        $this->shouldHaveType(GatewayFactory::class);
    }

}
