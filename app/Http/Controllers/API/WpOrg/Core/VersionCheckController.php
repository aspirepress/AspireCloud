<?php

namespace App\Http\Controllers\API\WpOrg\Core;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class VersionCheckController extends Controller
{
    /**
     * The currently installed version of WordPress.
     *
     * @var string
     */
    private string $currentVersion;

    /**
     * The locale for the request.
     *
     * @var string
     */
    private string $locale;

    public function __invoke(Request $request): JsonResponse
    {
        $this->currentVersion = $request->query('version') ?? (string) PHP_INT_MAX;
        $this->locale = $request->query('locale') ?? '';

        $response = new \stdClass();
        $response->offers = $this->buildOffers();
        $response->translations = $this->buildTranslations();

        return response()->json($response);
    }

    private function buildOffers() : array
    {
        $offers = [];
        $latestVersion = $this->getLatestVersion();

        if ($this->locale) {
            $offers[] = $this->buildTranslatedUpgradeOffer($latestVersion);

            // When a translated offer is needed, the API only adds a 'latest' en_US offer
            // if the currently installed version is not the latest version.
            if ($this->currentVersion !== $latestVersion) {
                $offers[] = $this->buildUpgradeOffer($latestVersion);
            }
        } else {
            $offers[] = $this->buildUpgradeOffer($latestVersion);
        }

        // Versions older than 5.1.19 get an extra upgrade offer.
        if (version_compare($this->currentVersion, '5.1.19', '<')) {
            $offers[] = $this->buildAutoupdateOffer('5.1.19');
        }

        $olderVersions = $this->getOlderVersions();
        foreach ($olderVersions as $olderVersion) {
            $offers[] = $this->buildAutoupdateOffer($olderVersion);
        }

        return $offers;
    }

    private function buildTranslatedUpgradeOffer(string $version): \stdClass
    {
        $offer = $this->buildUpgradeOffer($version);
        $offer->download = config('app.aspirecloud.download.base') . 'release/' . $this->locale . '/wordpress-' . $version . '.zip';
        $offer->locale = $this->locale;

        $offer->packages->full = config('app.aspirecloud.download.base') . 'release/' . $this->locale . '/wordpress-' . $version . '.zip';
        $offer->packages->no_content = false;
        $offer->packages->new_bundled = false;
        $offer->packages->partial = false;
        $offer->packages->rollback = false;

        return $offer;
    }

    private function buildAutoupdateOffer(string $version): \stdClass
    {
        $offer = $this->buildUpgradeOffer($version);

        $offer->response = 'autoupdate';
        $offer->new_files = $this->getHasNewFiles($version);

        return $offer;
    }

    private function buildUpgradeOffer(string $version): \stdClass
    {
        $offer = new \stdClass();

        $offer->response = $this->currentVersion === $version ? 'latest' : 'upgrade';
        $offer->download = config('app.aspirecloud.download.base') . 'release/wordpress-' . $version . '.zip';
        $offer->locale = $offer->response === 'latest' && ! $this->locale ? false : 'en_US';

        $offer->packages = new \stdClass();
        $offer->packages->full = config('app.aspirecloud.download.base') . 'release/wordpress-' . $version . '.zip';
        $offer->packages->no_content = config('app.aspirecloud.download.base') . 'release/wordpress-' . $version . '-no-content.zip';
        $offer->packages->new_bundled = config('app.aspirecloud.download.base') . 'release/wordpress-' . $version . '-new-bundled.zip';

        // Disabling these for now, pending more research from the team.
        $offer->packages->partial = false;
        $offer->packages->rollback = false;

        $offer->current = $version;
        $offer->version = $version;
        $offer->php_version = $this->getPhpVersion($version);
        $offer->mysql_version = $this->getMySqlVersion($version);
        $offer->new_bundled = $this->getNewBundled();
        $offer->partial_version = false;

        return $offer;
    }

    private function buildTranslations() : array
    {
        if (! $this->locale) {
            return [];
        }

        // There are other potential translations, possibly based on the slug.
        // For now, this just generates a single translation package.
        $translation = new \stdClass();
        $translation->type = 'core';
        $translation->slug = 'default';
        $translation->language = $this->locale;
        $translation->version = $this->currentVersion;
        $translation->updated = '2023-10-01T00:00:00Z'; // Store in DB from the translations file. Pull from DB for here.
        $translation->package = config('app.aspirecloud.download.base') . 'translation/core/' . $this->currentVersion . '/' . $this->locale . '.zip';
        $translation->autoupdate = true;

        return [ $translation ];
    }

    private function getLatestVersion(): string
    {
        // Probably pull from the database instead of hardcoding it.
        return '6.8.1';
    }

    private function getLatestMajorVersion(): string
    {
        // Probably pull from the database instead of hardcoding it.
        return '6.8';
    }

    private function getOlderVersions(): array
    {
        // Probably pull from the database instead of hardcoding these.
        // Should have the latest minor from every branch.
        // If not going for completeness, can probably remove the versions
        // below AspireUpdate's minimum WP version (currently 5.3).
        $map = [
            '6.8.1', '6.8', '6.7.2', '6.6.2', '6.6.5', '6.4.5', '6.3.5', '6.2.6', '6.1.7', '6.0.9',
            '5.9.10', '5.8.10', '5.7.12', '5.6.14', '5.5.15', '5.4.16', '5.3.18', '5.2.21', '5.1.19', '5.0.22',
            '4.9.26', '4.8.25', '4.7.29', '4.6.29', '4.5.32', '4.4.33', '4.3.34', '4.2.38', '4.1.41', '4.0.38',
            '3.9.40', '3.8.41', '3.7.41', '3.6.1', '3.5.2', '3.4.2', '3.3.3', '3.2.1', '3.1.4', '3.0.6',
            '2.9.2', '2.8.6', '2.7.1', '2.6.5', '2.5.1', '2.3.3', '2.2.3', '2.1.3', '2.0.11',
            '1.5.2', '1.2.2', '1.0.2',
            '0.72',
        ];

        // If no version has been provided, all versions are considered older.
        if ($this->currentVersion === (string) PHP_INT_MAX) {
            return $map;
        }

        $versions = [];
        foreach ($map as $version) {
            if (version_compare($this->currentVersion, $version, '<')) {
                $versions[] = $version;
            }
        }

        return $versions;
    }

    private function getPhpVersion(string $version): string
    {
        // The WordPress versions that started a new minimum PHP version requirement.
        $map = [
            '6.6' => '7.2.24',
            '6.3' => '7.0.0',
            '5.2' => '5.6.20',
            '4.1' => '5.2.4',
        ];

        foreach ($map as $wpMajor => $php) {
            if (version_compare($version, $wpMajor, '>=')) {
                return $php;
            }
        }

        return end($map);
    }

    private function getMySqlVersion(string $version): string
    {
        // The WordPress versions that started a new minimum MySQL version requirement.
        $map = [
            '6.5' => '5.5.5',
            '4.1' => '5.0',
        ];

        foreach ($map as $wpMajor => $mysql) {
            if (version_compare($version, $wpMajor, '>=')) {
                return $mysql;
            }
        }

        return end($map);
    }

    private function getNewBundled(): string
    {
        // Appears to be the second latest major version.
        $parts = explode('.', $this->getLatestMajorVersion());
        if ($parts[1] > 0) {
            --$parts[1];
        } else {
            $parts[1] = 9;
            --$parts[0];
        }

        return $parts[0] . '.' . $parts[1];
    }

    private function getHasNewFiles(string $version): bool
    {
        // WP Core relaxes filesystem checks when the API
        // specifies that it's safe to do.
        // The API specifies this by setting new_files to false.
        // For now, return true so filesystem checks are not relaxed.
        return true;

        // Appears to depend on a comparison between
        // the installed version and the offered version.
        $hasNewFiles = [];
        return array_key_exists($version, $hasNewFiles);
    }
}
