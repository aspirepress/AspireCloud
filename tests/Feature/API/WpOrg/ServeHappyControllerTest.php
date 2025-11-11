<?php
declare(strict_types=1);

it('serves happy', function () {
    $response = $this->get('/core/serve-happy/1.0?php_version=8.3');

    $response
        ->assertStatus(200)
        ->assertJson([
            "recommended_version" => "7.4",
            "minimum_version" => "7.2.24",
            "is_supported" => true,
            "is_secure" => true,
            "is_acceptable" => true,
        ]);
});

it('shows false for insecure versions', function () {
    $response = $this->get('/core/serve-happy/1.0?php_version=5.3');

    $response
        ->assertStatus(200)
        ->assertJson([
            "recommended_version" => "7.4",
            "minimum_version" => "7.2.24",
            "is_supported" => false,
            "is_secure" => false,
            "is_acceptable" => false,
        ]);
});

it('requires php_version param', function () {
    // upstream throws 400 and returns a json error, but wp only checks for a 200 status, so these are fine
    $response = $this->get('/core/serve-happy/1.0');
    $response->assertStatus(302); // laravel's validation failure behavior for non-json requests
    $response = $this->get('/core/serve-happy/1.0', ['Accept' => 'application/json']);
    $response->assertStatus(422);
});
