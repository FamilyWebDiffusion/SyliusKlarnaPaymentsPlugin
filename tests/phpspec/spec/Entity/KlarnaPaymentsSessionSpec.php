<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaPaymentsSession;
use PhpSpec\ObjectBehavior;

class KlarnaPaymentsSessionSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(KlarnaPaymentsSession::class);
    }

    function it_is_not_created_by_default(): void
    {
        $this->shouldNotBeCreated();
    }

    function it_gets_session_id(): void
    {
        $this->setSessionId('session_id');
        $this->getSessionId()->shouldReturn('session_id');
    }

    function it_gets_client_token_so_it_is_created(): void
    {
        $this->setClientToken('token');
        $this->getClientToken()->shouldReturn('token');
        $this->shouldBeCreated();
    }

    function it_gets_payment_method_categories(): void
    {
        $this->setPaymentMethodCategories([
            ['identifier' => 'category1', 'name' => 'Category 1'],
            ['identifier' => 'category2', 'name' => 'Category 2']
        ]);
        $this->getPaymentMethodCategories()->shouldReturn([
            ['identifier' => 'category1', 'name' => 'Category 1'],
            ['identifier' => 'category2', 'name' => 'Category 2']
        ]);
        $this->getPaymentMethodCategoriesIdentifiers()->shouldReturn(['category1','category2']);
    }
}
