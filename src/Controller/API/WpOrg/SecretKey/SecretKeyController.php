<?php

namespace App\Controller\API\WpOrg\SecretKey;

use App\Controller\BaseController;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecretKeyController extends BaseController
{
    // From https://github.com/wp-cli/config-command/blob/main/src/Config_Command.php
    public const string VALID_KEY_CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';

    #[Route('/secret-key/{version}', name: 'api_secretkey')]
    public function index(string $version): Response
    {
        $response = match ($version) {
            '1.0' => $this->generateKey_1_0(),
            '1.1' => $this->generateKey_1_1(),
            default => $this->createNotFoundException('unsupported version'),
        };
        return new Response($response, 200, ['Content-Type' => 'text/plain']);
    }

    #[Route('/secret-key/{version}/salt', name: 'api_secretkey_salt')]
    public function salt(): Response
    {
        return new Response($this->generateSalt_1_1(), 200, ['Content-Type' => 'text/plain']);
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
                     'AUTH_KEY',
                     'SECURE_AUTH_KEY',
                     'LOGGED_IN_KEY',
                     'NONCE_KEY',
                     'AUTH_SALT',
                     'SECURE_AUTH_SALT',
                     'LOGGED_IN_SALT',
                     'NONCE_SALT',
                 ] as $name) {
            $param = "'$name',";
            $out .= sprintf("define(%-19s '%s');\n", $param, self::uniqueKey());
        }
        return $out;
    }

    private static function uniqueKey(int $length = 64): string
    {
        return implode(array_map(
            static fn() => self::VALID_KEY_CHARACTERS[random_int(0, $length)],
            array_fill(0, $length, null),
        ));
    }
}
