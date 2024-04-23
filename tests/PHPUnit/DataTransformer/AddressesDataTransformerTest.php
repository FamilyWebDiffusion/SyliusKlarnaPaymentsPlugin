<?php

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\PHPUnit\DataTransformer;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\AddressesDataTransformer;
use PHPUnit\Framework\TestCase;

class AddressesDataTransformerTest extends TestCase
{
    /**
     * @dataProvider phoneNumberProvider
     */
    public function testformatPhoneNumber($expected, $phoneNumber, $countryCode)
    {
        $transformer = new AddressesDataTransformer();
        self::assertSame($expected, $transformer->formatPhoneNumber($phoneNumber, $countryCode));

    }

    public function phoneNumberProvider(): array
    {
        return [
            ['+491755555555', '0175 5555555', 'DE'],
            ['+4951057250946', '051057 250946', 'DE'],
            ['+495028997840', '050 28997840', 'DE'],
            ['+33609123456', '0609123456', 'FR'],
            ['+33609123456', '06.09.12.34.56', 'FR'],
            ['+447700760695', '07700 760695', 'GB'],
            ['+442079460695', '20 7946 0695', 'GB'],
            ['+447182193644', '(07182) 193644', 'GB'],
            ['+61785460586', '(07) 8546 0586', 'AU'],
            ['+15024571832', '502-457-1832', 'US'],
            ['+19109843499', '910-984-3499', 'US'],
            ['+12123813738', '212-381-3738', 'US'],
            [null, '256', 'US'],
            [null, '', 'US'],
            [null, null, 'US'],
            [null, '-', 'DE'],
            [null, 'no disclose', 'US'],
            [null, '0000', 'FR']
        ];
    }
}
