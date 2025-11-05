<?php
declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('cve:query')
        ->everyTenMinutes()
        ->name('cve-query-all-latest-releases-10min')
        ->withoutOverlapping(5)
        ->runInBackground()
        ->when(function () {
            // Only run this frequently if we have HIGH severity vulnerabilities
            return \App\Models\Labels::where('value', 'high')->exists();
        })
        ->onFailure(function () {
            \Log::error('CVE query (10min interval) failed');
        });

// Run command every 30 minutes
// This will check ALL latest releases
Schedule::command('cve:query')
        ->everyThirtyMinutes()
        ->name('cve-query-all-latest-releases-30min')
        ->withoutOverlapping(10)
        ->runInBackground()
        ->when(function () {
            // Only run if we have MEDIUM (but no HIGH) severity vulnerabilities
            $hasHigh = \App\Models\Labels::where('value', 'high')->exists();
            $hasMedium = \App\Models\Labels::where('value', 'medium')->exists();
            return !$hasHigh && $hasMedium;
        })
        ->onFailure(function () {
            \Log::error('CVE query (30min interval) failed');
        });

// Run command every hour
// This will check ALL latest releases
Schedule::command('cve:query')
        ->hourly()
        ->name('cve-query-all-latest-releases-hourly')
        ->withoutOverlapping(15)
        ->runInBackground()
        ->when(function () {
            // Only run if we have LOW (but no HIGH/MEDIUM) severity vulnerabilities
            $hasHigh = \App\Models\Labels::where('value', 'high')->exists();
            $hasMedium = \App\Models\Labels::where('value', 'medium')->exists();
            $hasLow = \App\Models\Labels::where('value', 'low')->exists();
            return !$hasHigh && !$hasMedium && $hasLow;
        })
        ->onFailure(function () {
            \Log::error('CVE query (hourly interval) failed');
        });

// Run command every 2 hours
// This will check ALL latest releases
Schedule::command('cve:query')
        ->everyTwoHours()
        ->name('cve-query-all-latest-releases-2hours')
        ->withoutOverlapping(30)
        ->runInBackground()
        ->when(function () {
            // Only run if we have NO vulnerabilities at all, or only 'none' value
            $hasAnyVuln = \App\Models\Labels::whereIn('value', ['high', 'medium', 'low'])->exists();
            return !$hasAnyVuln;
        })
        ->onFailure(function () {
            \Log::error('CVE query (2hour interval) failed');
        });

// Daily full scan as a fallback/safety net
// Runs regardless of vulnerability value
Schedule::command('cve:query')
        ->daily()
        ->at('03:00')
        ->name('cve-query-daily-safety-check')
        ->withoutOverlapping(60)
        ->runInBackground()
        ->onSuccess(function () {
            \Log::info('Daily CVE safety check completed successfully');
        })
        ->onFailure(function () {
            \Log::error('Daily CVE safety check failed');
        });
