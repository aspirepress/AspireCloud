<?php
declare(strict_types=1);

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
            if (str_ends_with($line, "\n")) {
                $line = substr($line, 0, -1);
            }
            yield $line;
        }
    }

    private function __construct()
    {
        // not instantiable
    }
}
