<?php

declare(strict_types=1);

namespace App\Http;

use App\Utils\JSON;
use Generator;
use Illuminate\Http\Request;

trait JsonLines
{
    public function lazyJsonLines(Request $request): Generator
    {
        $handle = $request->getContent(asResource: true);
        try {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (!$line) {
                    continue;
                }
                yield JSON::toAssoc($line);
            }
        } finally {
            \Safe\fclose($handle);
        }
    }
}
