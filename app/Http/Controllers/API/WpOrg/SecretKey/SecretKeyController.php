<?php

namespace App\Http\Controllers\API\WpOrg\SecretKey;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SecretKeyController extends Controller
{
    // From https://github.com/wp-cli/config-command/blob/main/src/Config_Command.php
    public const VALID_KEY_CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';

    public function index(string $version, Request $request): Response
    {
        $response = match ($version) {
            '1.0' => $this->generate_1_0(),
            '1.1' => $this->generate_1_1(),
            default => throw new NotFoundHttpException('unsupported version'),
        };

        return response($response, 200, ['Content-Type' => 'text/plain']);
    }

    public function salt(): Response
    {
        return response($this->generate_salt_1_1(), 200, ['Content-Type' => 'text/plain']);
    }

    private function generate_1_0(): string
    {
        $key = self::unique_key();
        return "define('SECRET_KEY', '$key');\n";
        // define('SECRET_KEY', '/zG$R}A5yD(&R2ob_,{g#N\\%d&MoV=NpFNFl%,e*0Zx~\"CMim^6hgQm=e20%n@er');
    }

    private function generate_1_1(): string
    {
        $out = '';
        foreach (['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY'] as $name) {
            $param = "'$name',";
            $out .= sprintf("define(%-18s '%s');\n", $param, self::unique_key());
        }
        return $out;
        // define('AUTH_KEY',        'y*k]cp10`=Ut@0G9GwLD]nYX-#L_dkFyQ=7ts{,=R<T;0N/KmQ>+@Z!Up|5PQIp9');
        // define('SECURE_AUTH_KEY', '{4d<t m-ktqda/*B[C{2x#;LM{rU`N-.uPMg*d-jpxk<<aW4@j{=uo|E4^dBn+zD');
        // define('LOGGED_IN_KEY',   '`Q7aZ[Hg9K7OYv(v:{sb6$jf]BKZ)<$)F- *8Js6Nt<IOXbV*F61-Df;@{B1%?v*');
        // define('NONCE_KEY',       '.rru94)V[pPQ&?Vt.qQ)wY)Wu{^cL]2*q8FDO!Z2. Y-3$HQY8s@)quO0G+gd{l$');
    }

    private function generate_salt_1_1(): string
    {
        $out = '';
        foreach ([
            'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
            'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT',
        ] as $name) {
            $param = "'$name',";
            $out .= sprintf("define(%-19s '%s');\n", $param, self::unique_key());
        }
        return $out;
        // define('AUTH_KEY',         '1c<iaBt`UeX,.s*Au~o;4v2 +4=KQFnn#YGLs<pkK{=5Nart[]r6_*U?-y,06AJF');
        // define('SECURE_AUTH_KEY',  '-_bS,;!&{+Qr|z~$N6cueFO<!|Kx12!cJ7Lx-gMZ9ekSj+O2=,wRZ=2vy?r2-iLw');
        // define('LOGGED_IN_KEY',    '=I6gvNz[$l[|_}o[vai+.zd>zc%{cAx{4L%V-w_eLBo,S- Y !LU!QpUZ=xv<rkp');
        // define('NONCE_KEY',        '@{(PEa5 0q_-?dd*lb.<l5rONcT|zbohI?~8!x55W58$1KDS,LeH|{#7Z-0-_<R3');
        // define('AUTH_SALT',        '0@jMX6(oj>M?q-@H_.dSjEyK=}_O+ u[@I2S!lA?8v}7HrL/I.{p07U3<0Dzb|p`');
        // define('SECURE_AUTH_SALT', '{sAB~6ta%o8o3|`$!8I5$`fS7M3X9VTX1*ZoX&/9_b^QID+pbds^.HYEewz^xCwB');
        // define('LOGGED_IN_SALT',   'kj,?{%-qhT}#P Q?+oSjLN;^cZu=,V2ZjSHI;XgU-h@`H+?1?::muJ*4--&~!$.+');
        // define('NONCE_SALT',       ':nUJz*%EU1R0 3x<8`=:>PC+^Vhk9DjyjI@tjuMVOj@dk(N_jn-(+AC/I7wOs`yT');
    }

    private static function unique_key(int $length = 64): string
    {
        $chars = self::VALID_KEY_CHARACTERS;
        $key   = '';
        $len = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $key .= $chars[random_int(0, $len)];
        }

        return $key;
    }

}
