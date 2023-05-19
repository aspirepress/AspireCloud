<?php

namespace AppTest\Acceptance;

use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

class SampleContext implements Context
{
    /**
     * @Given that the world has not ended
     */
    public function worldNotEnded(): void
    {
        Assert::eq(self::class, self::class);
    }

    /**
     * @Then true still equals true
     */
    public function sampleTestLine(): void
    {
        Assert::true(true);
    }
}
