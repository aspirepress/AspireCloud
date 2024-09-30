<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Unit\Data\Values;

use AspirePress\Cdn\Data\Values\Version;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class VersionTest extends TestCase
{
    /**
     * @dataProvider versionDataProvider
     */
    public function testCreatingVersion(string $version): void
    {
        $parts      = explode('.', $version);
        $totalParts = count($parts);

        $sut = Version::fromString($version);

        switch ($totalParts) {
            case 4:
                $this->assertEquals($parts[0], $sut->getMajor());
                $this->assertEquals($parts[1], $sut->getMinor());
                $this->assertEquals($parts[2], $sut->getPatch());
                $this->assertEquals($parts[3], $sut->getSecurity());
                $this->assertEquals($version, $sut->getVersion());
                $this->assertEquals($version, (string) $sut);
                break;

            case 3:
                $this->assertEquals($parts[0], $sut->getMajor());
                $this->assertEquals($parts[1], $sut->getMinor());
                $this->assertEquals($parts[2], $sut->getPatch());
                $this->assertEquals($version, $sut->getVersion());
                $this->assertEquals($version, (string) $sut);
                break;

            case 2:
                $this->assertEquals($parts[0], $sut->getMajor());
                $this->assertEquals($parts[1], $sut->GetMinor());
                $this->assertEquals($version, (string) $sut);
                break;

            case 1:
                $this->assertEquals($parts[0], $sut->getMajor());
                $this->assertEquals($version, $sut->getVersion());
                $this->assertEquals($version, (string) $sut);
                break;

            default:
                throw new RuntimeException('We should have not got here; invalid version provided to test');
        }
    }

    /**
     * @dataProvider versionComparisonDataProvider
     */
    public function testVersionComparison(string $testVersion, string $currentVersion, bool $expected): void
    {
        $testVersion = Version::fromString($testVersion);
        $sut         = Version::fromString($currentVersion);

        $this->assertSame($expected, $sut->versionNewerThan($testVersion));
    }

    /**
     * @dataProvider  versionMismatches
     */
    public function testExceptionsRaisedIfUsersVersionExceedsCurrentVersion(string $testVersion, string $currentVersion): void
    {
        $this->expectException(InvalidArgumentException::class);

        $sut         = Version::fromString($currentVersion);
        $testVersion = Version::fromString($testVersion);

        $sut->versionNewerThan($testVersion);
    }

    /**
     * @dataProvider versionStrings
     */
    public function testVersionNumbersCanContainZeros(string $versionToTest): void
    {
        $sut = Version::fromString($versionToTest);
        $this->assertEquals($versionToTest, $sut->getVersion());
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function versionDataProvider(): array
    {
        return [
            ['1'],
            ['1.2'],
            ['1.2.3'],
            ['1.2.3.4'],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function versionComparisonDataProvider(): array
    {
        return [
            [
                '1', // Test Version
                '2', // Current Version
                true, // If current version should be bigger than test version
            ],
            [
                '1', // Test Version
                '1', // Current Version
                false, // If current version should be bigger than test version
            ],
            [
                '2.1', // Test Version
                '2.2', // Current Version
                true, // If current version should be bigger than test version
            ],
            [
                '2.1.0', // Test Version
                '2.1.1', // Current Version
                true, // If current version should be bigger than test version
            ],
            [
                '2.1.0', // Test Version
                '2.1.0', // Current Version
                false, // If current version should be bigger than test version
            ],
            [
                '2.1.0.0', // Test Version
                '2.1.0.1', // Current Version
                true, // If current version should be bigger than test version
            ],
            [
                '2.2.0.1', // Test Version
                '2.2.3.1', // Current Version
                true, // If current version should be bigger than test version
            ],
            [
                '2.2.0.0', // Test Version
                '2.2.0.0', // Current Version
                false, // If current version should be bigger than test version
            ],
            [
                '2.2.0.1', // Test Version
                '3.0.0.0', // Current Version
                true, // If current version should be bigger than test version
            ],
            [
                '2.2.0.1', // Test Version
                '2.2.1.0', // Current Version
                true, // If current version should be bigger than test version
            ],
            [
                '2.2.0.1', // Test Version
                '2.2.0.2', // Current Version
                true, // If current version should be bigger than test version
            ],
            [
                '2.2.0.1', // Test Version
                '2.3.0.0', // Current Version
                true, // If current version should be bigger than test version
            ],
            [
                '2.2.0.1', // Test Version
                '2.2.2.1', // Current Version
                true, // If current version should be bigger than test version
            ],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function versionMismatches(): array
    {
        return [
            ['3', '2'],
            ['2.1', '2.0'],
            ['2.1.2.0', '2.1.0.0'],
            ['2.1.1.1', '2.1.1.0'],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function versionStrings(): array
    {
        return [
            ['1.0.0.0'],
            ['1.0.1.0'],
            ['1.0'],
            ['1.0.1'],
            ['1.0.0.1'],
            ['1.1.0.0'],
            ['0.0.1.0'],
        ];
    }
}
