<?php

use App\Models\WpOrg\ClosedPlugin;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    ClosedPlugin::truncate();
    Storage::fake('s3');
});

function export_closed_plugins_uri(array $params = []): string
{
    $uri = '/export/closed_plugins';
    if (!empty($params)) {
        $uri .= '?' . http_build_query($params);
    }
    return $uri;
}

it('returns all closed plugins if "after" arg is not specified', function () {
    $closedPlugin1 = ClosedPlugin::factory()->create(['ac_created' => '2025-01-01 00:00:00']);
    $closedPlugin2 = ClosedPlugin::factory()->create(['ac_created' => '2025-03-03 00:00:00']);

    $response = $this
        ->get(export_closed_plugins_uri());

    $response->assertStatus(200);
    $content = $response->streamedContent();

    $lines = explode("\n", trim($content));
    expect($lines)->toHaveCount(2);

    $data1 = \Safe\json_decode($lines[0], true);
    $data2 = \Safe\json_decode($lines[1], true);

    expect($data1)->toEqual($closedPlugin1->ac_raw_metadata);
    expect($data2)->toEqual($closedPlugin2->ac_raw_metadata);
});

it('returns only closed plugins with "ac_created" >= "after" arg', function () {
    ClosedPlugin::factory()->create(['ac_created' => '2024-12-12 00:00:00']);
    $closedPlugin1 = ClosedPlugin::factory()->create(['ac_created' => '2025-01-01 00:00:00']);
    $closedPlugin2 = ClosedPlugin::factory()->create(['ac_created' => '2025-03-03 00:00:00']);

    $response = $this
        ->get(export_closed_plugins_uri(['after' => '2025-01-01']));

    $response->assertStatus(200);
    $content = $response->streamedContent();

    $lines = explode("\n", trim($content));
    expect($lines)->toHaveCount(2);

    $data1 = \Safe\json_decode($lines[0], true);
    $data2 = \Safe\json_decode($lines[1], true);

    expect($data1)->toEqual($closedPlugin1->ac_raw_metadata);
    expect($data2)->toEqual($closedPlugin2->ac_raw_metadata);
});

it('returns an error if invalid arg format is used in the export closed plugins request', function () {
    ClosedPlugin::factory()->create(['ac_created' => '2025-01-01 00:00:00']);

    $response = $this
        ->getJson(export_closed_plugins_uri(['after' => '2025-01-01 00:00:00']));

    $response->assertStatus(422);
    $response->assertJson(['message' => 'The after field format is invalid.']);

    $response = $this
        ->getJson(export_closed_plugins_uri(['after' => '20250101']));

    $response->assertStatus(422);
    $response->assertJson(['message' => 'The after field format is invalid.']);
});
