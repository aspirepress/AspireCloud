<?php

declare(strict_types=1);

namespace AspirePress\AspireCloudFunctional;

use PHPUnit\Framework\TestCase;

class SampleFunctionalTest extends TestCase
{
    public function testSubtraction(): void
    {
        $this->assertEquals(2, 4 - 2);
    }
}
