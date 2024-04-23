<?php

declare(strict_types=1);

namespace Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Behat\Context\Common;

use Behat\MinkExtension\Context\MinkContext;

/**
 * Custom context use to extend the general base MinkContext
 * Do not use this context directly in suites (or avoid contexts extending it)
 */
class FeatureContext extends MinkContext
{
    /**
     * @Given print last response in :filename
     */
    public function printLastResponseInFile($filename): void
    {
        file_put_contents(__DIR__ . '/../../../../' . $filename, $this->getSession()->getPage()->getContent());
    }
}
