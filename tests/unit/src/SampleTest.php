<?php

declare(strict_types=1);

namespace AppTest;

use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    public function testTwoPlusTwo(): void
    {
        $this->assertEquals(4, 2 + 2);
    }
}
