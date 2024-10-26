<?php

namespace App\Data\WpOrg\Themes;

use App\Data\WpOrg\PageInfo;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Data;
use stdClass;

class QueryThemesResponse extends Data
{
    /**
     * @param PageInfo $pageInfo
     * @param array<string,mixed> $themes   // TODO: use Collection<ThemeResponse>
     */
    public function __construct(
        #[MapOutputName('info')]
        public readonly PageInfo $pageInfo,
        #[Present]
        public readonly array $themes,
    ) {}

    /** for API version 1.0 responses only -- do not use this otherwise! */
    public function toStdClass(): stdClass
    {
        return (object) ['info' => $this->pageInfo->toArray(), 'themes' => $this->themes];
    }
}
