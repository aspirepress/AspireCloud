<?php

use App\Models\WpOrg\Plugin;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Plugin::truncate();
    Storage::fake('s3');
});

function export_plugins_uri(array $params = []): string
{
    $uri = '/export/plugins';
    if (!empty($params)) {
        $uri .= '?' . http_build_query($params);
    }
    return $uri;
}

// Export plugins tests
it('returns all plugins if "after" arg is not specified', function () {
    $plugin1 = Plugin::factory()->create(['ac_created' => '2025-01-01 00:00:00']);
    $plugin2 = Plugin::factory()->create(['ac_created' => '2025-03-03 00:00:00']);

    $response = $this
        ->get(export_plugins_uri());

    $response->assertStatus(200);
    $content = $response->streamedContent();

    $lines = explode("\n", trim($content));
    expect($lines)->toHaveCount(2);

    $data1 = \Safe\json_decode($lines[0], true);
    $data2 = \Safe\json_decode($lines[1], true);

    expect($data1)->toEqual($plugin1->ac_raw_metadata);
    expect($data2)->toEqual($plugin2->ac_raw_metadata);
});

it('returns only plugins with "ac_created" >= "after" arg', function () {
    Plugin::factory()->create(['ac_created' => '2024-12-12 00:00:00']);
    $plugin1 = Plugin::factory()->create(['ac_created' => '2025-01-01 00:00:00']);
    $plugin2 = Plugin::factory()->create(['ac_created' => '2025-03-03 00:00:00']);

    $response = $this
        ->get(export_plugins_uri(['after' => '2025-01-01']));

    $response->assertStatus(200);
    $content = $response->streamedContent();

    $lines = explode("\n", trim($content));
    expect($lines)->toHaveCount(2);

    $data1 = \Safe\json_decode($lines[0], true);
    $data2 = \Safe\json_decode($lines[1], true);

    expect($data1)->toEqual($plugin1->ac_raw_metadata);
    expect($data2)->toEqual($plugin2->ac_raw_metadata);
});

it('returns an error if invalid arg format is used in the export plugins request', function () {
    Plugin::factory()->create(['ac_created' => '2025-01-01 00:00:00']);

    $response = $this
        ->get(export_plugins_uri(['after' => '2025-01-01 00:00:00']));

    $response->assertStatus(400);
    $response->assertJson(['error' => 'Invalid request']);
});
