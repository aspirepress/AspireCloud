<?php

use Spatie\LaravelData\Data;

class TranslationData extends Data
{
    public function __construct(
        public string $locale,
        public string $translation_key,
        public string $text,
    ) {}
}
