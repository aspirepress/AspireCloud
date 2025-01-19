<?php

namespace App\Http\Controllers\API\WpOrg\SecretKey;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Random\RandomException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SecretKeyController extends Controller
{
    // From https://github.com/wp-cli/config-command/blob/main/src/Config_Command.php
    public const VALID_KEY_CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';

    /**
     * @throws RandomException
     */
    public function index(string $version): Response
    {
        $response = match ($version) {
            '1.0' => $this->generateKey_1_0(),
            '1.1' => $this->generateKey_1_1(),
            default => throw new NotFoundHttpException('unsupported version'),
        };

        return response($response, 200, ['Content-Type' => 'text/plain']);
    }

    public function salt(): Response
    {
        return response($this->generateSalt_1_1(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * @throws RandomException
     */
    private function generateKey_1_0(): string
    {
        $key = self::uniqueKey();

        return "define('SECRET_KEY', '$key');\n";
    }

    private function generateKey_1_1(): string
    {
        $out = '';
        foreach (['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY'] as $name) {
            $param = "'$name',";
            $out .= sprintf("define(%-18s '%s');\n", $param, self::uniqueKey());
        }
        return $out;
    }

    private function generateSalt_1_1(): string
    {
        $out = '';
        foreach ([
            'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
            'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT',
        ] as $name) {
            $param = "'$name',";
            $out .= sprintf("define(%-19s '%s');\n", $param, self::uniqueKey());
        }

        return $out;
    }

    /**
     * @throws RandomException
     */
    private static function uniqueKey(int $length = 64): string
    {
        return implode(array_map(
            static fn() => self::VALID_KEY_CHARACTERS[random_int(0, $length)],
            array_fill(0, $length, null),
        ));
    }
}
