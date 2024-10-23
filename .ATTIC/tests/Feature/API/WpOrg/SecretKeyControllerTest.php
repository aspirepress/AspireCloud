<?php

namespace Tests\Feature\API\WpOrg;

use App\Http\Controllers\API\WpOrg\SecretKey\SecretKeyController;
use Exception;

$validKeys = SecretKeyController::VALID_KEY_CHARACTERS;

// Helper function to validate keys
function validateKeys(string $content, array $expectedKeyNames, string $validKeys): void
{
    foreach ($expectedKeyNames as $keyName) {
        preg_match("/define\('$keyName',\s+'([^']+)'\);/", $content, $matches);

        // Ensure we have a match and a captured group
        expect($matches)->toHaveCount(2);

        // Extract the key value from the matches
        $keyValue = $matches[1];

        // Validate that the key contains only valid characters
        expect(preg_match('/^[' . preg_quote($validKeys, '/') . ']{64}$/', $keyValue))
            ->toBe(1)
            ->and(preg_match(
                '/^define\(\'' . preg_quote(
                    $keyName,
                    '/'
                ) . '\',\s+\'[^\']+\'\);$/',
                $matches[0]
            ))->toBe(1);
    }
}

it(
    'can generate a secret keys for version 1.0 and 1.1',
    function (string $version, int $expectedKeys) use ($validKeys) {
        $response = $this->getJson("/secret-key/$version");

        expect($response->getStatusCode())
            ->toBe(200)
            ->and($response->headers->get('Content-Type'))->toContain('text/plain');

        $expectedKeyNames = match ($version) {
            '1.0' => [ 'SECRET_KEY' ],
            '1.1' => [ 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY' ],
        };

        $content = $response->getContent();

        // Validate the number of keys, the +1 is for the last line break
        expect(explode("\n", $content))->toHaveCount($expectedKeys + 1);

        validateKeys($content, $expectedKeyNames, $validKeys);
    }
)->with([
    '1.0 version' => [ '1.0', 1 ],
    '1.1 version' => [ '1.1', 4 ],
]);

it('can generate a secret keys with salt for version 1.1', function () use ($validKeys) {
    $response = $this->getJson('/secret-key/1.1/salt');

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->headers->get('Content-Type'))->toContain('text/plain');

    $content = $response->getContent();

    // Validate the number of keys, the +1 is for the last line break
    expect(explode("\n", $content))->toHaveCount(8 + 1);

    $expectedKeyNames = [
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT',
    ];

    validateKeys($content, $expectedKeyNames, $validKeys);
});

it('returns 404 for unsupported salt versions', function () {
    $response = $this->getJson('/secret-key/1.0/salt');
    expect($response->getStatusCode())->toBe(404);
});

it('returns 404 for unsupported secret key versions', function () {
    $response = $this->getJson('/secret-key/2.0');
    expect($response->getStatusCode())->toBe(404);
});
