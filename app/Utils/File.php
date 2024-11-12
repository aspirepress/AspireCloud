<?php

namespace App\Utils;

use Generator;
use SplFileObject;

final class File
{
    public static function lazyLines(string $filename): Generator
    {
        $file = new SplFileObject($filename);
        while (!$file->eof()) {
            $line = $file->fgets();
            yield substr($line, 0, -1);
        }
    }

    private function __construct()
    {
        // not instantiable
    }
}
