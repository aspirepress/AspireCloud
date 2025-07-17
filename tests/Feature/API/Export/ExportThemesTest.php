<?php

use App\Models\WpOrg\Theme;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Theme::truncate();
    Storage::fake('s3');
});

function export_themes_uri(array $params = []): string
{
    $uri = '/export/themes';
    if (!empty($params)) {
        $uri .= '?' . http_build_query($params);
    }
    return $uri;
}

it('returns all themes if "after" arg is not specified', function () {
    $theme1 = Theme::factory()
        ->create(['ac_created' => '2025-01-01 00:00:00']);
    $theme2 = Theme::factory()
        ->create(['ac_created' => '2025-03-03 00:00:00']);

    $response = $this
        ->get(export_themes_uri());

    $response->assertStatus(200);
    $content = $response->streamedContent();

    $lines = explode("\n", trim($content));
    expect($lines)->toHaveCount(2);

    $data1 = \Safe\json_decode($lines[0], true);
    $data2 = \Safe\json_decode($lines[1], true);

    expect($data1)->toEqual($theme1->ac_raw_metadata);
    expect($data2)->toEqual($theme2->ac_raw_metadata);
});

it('returns only themes with "ac_created" >= "after" arg', function () {
    Theme::factory()
        ->create(['ac_created' => '2024-12-12 00:00:00']);

    $theme1 = Theme::factory()
        ->create(['ac_created' => '2025-01-01 00:00:00']);
    $theme2 = Theme::factory()
        ->create(['ac_created' => '2025-03-03 00:00:00']);

    $response = $this
        ->get(export_themes_uri(['after' => '2025-01-01']));

    $response->assertStatus(200);
    $content = $response->streamedContent();

    $lines = explode("\n", trim($content));
    expect($lines)->toHaveCount(2);

    $data1 = \Safe\json_decode($lines[0], true);
    $data2 = \Safe\json_decode($lines[1], true);

    expect($data1)->toEqual($theme1->ac_raw_metadata);
    expect($data2)->toEqual($theme2->ac_raw_metadata);
});

it('returns an error if invalid arg format is used in the export themes request', function () {
    Theme::factory()
        ->create(['ac_created' => '2025-01-01 00:00:00']);

    $response = $this
        ->get(export_themes_uri(['after' => '2025-01-01 00:00:00']));

    $response->assertStatus(400);
    $response->assertJson(['error' => 'Invalid request']);
});
