<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud\Data\Values;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class Version
{
    private function __construct(
        private int $major,
        private ?int $minor,
        private ?int $patch,
        private ?int $security
    ) {
    }

    public static function fromString(string $version): self
    {
        $parts = explode('.', $version);

        Assert::allNumeric($parts);

        $major    = null;
        $minor    = null;
        $patch    = null;
        $security = null;

        switch (count($parts)) {
            case 1:
                $major = (int) $parts[0];
                break;

            case 2:
                $major = (int) $parts[0];
                $minor = (int) $parts[1];
                break;

            case 3:
                $major = (int) $parts[0];
                $minor = (int) $parts[1];
                $patch = (int) $parts[2];
                break;

            case 4:
                $major    = (int) $parts[0];
                $minor    = (int) $parts[1];
                $patch    = (int) $parts[2];
                $security = (int) $parts[3];
                break;

            default:
                // We should never get here!
                throw new InvalidArgumentException('Invalid version provided!');
        }

        return new self(
            $major,
            $minor,
            $patch,
            $security
        );
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getMinor(): ?int
    {
        return $this->minor;
    }

    public function getPatch(): ?int
    {
        return $this->patch;
    }

    public function getSecurity(): ?int
    {
        return $this->security;
    }

    public function getVersion(): string
    {
        $major    = $this->major;
        $minor    = $this->minor;
        $patch    = $this->patch;
        $security = $this->security;

        $versionString = (string) $major;

        if (! ($minor === null)) {
            $versionString .= '.' . $minor;
        }

        if (! ($patch === null)) {
            $versionString .= '.' . $patch;
        }

        if (! ($security === null)) {
            $versionString .= '.' . $security;
        }

        return $versionString;
    }

    public function __toString(): string
    {
        return $this->getVersion();
    }

    public function versionNewerThan(Version|string $version): bool
    {
        $testVersion = $version;
        if (is_string($version)) {
            $testVersion = self::fromString($version);
        }

        // Check that the test version is not greater than the current version.
        Assert::false($testVersion->getMajor() > $this->getMajor());
        Assert::false($testVersion->getMajor() === $this->getMajor() && $testVersion->getMinor() > $this->getMinor());
        Assert::false($testVersion->getMajor() === $this->getMajor() && $testVersion->getMinor() === $this->getMinor() && $testVersion->getPatch() > $this->getPatch());
        Assert::false($testVersion->getMajor() === $this->getMajor() && $testVersion->getMinor() === $this->getMinor() && $testVersion->getPatch() === $this->getPatch() && $testVersion->getSecurity() > $this->getSecurity());

        // Test versions for comparison, flagging any that are different
        $majorNewer    = $this->getMajor() === $testVersion->getMajor();
        $minorNewer    = $this->getMinor() === $testVersion->getMinor();
        $patchNewer    = $this->getPatch() === $testVersion->getPatch();
        $securityNewer = $this->getSecurity() === $testVersion->getSecurity();

        return ! ($majorNewer && $minorNewer && $patchNewer && $securityNewer);
    }
}
