<?php

namespace App\Data\WpOrg\Themes;

use App\Data\WpOrg\AbstractWpOrgResponse;
use App\Data\WpOrg\PageInfo;
use stdClass;

readonly class QueryThemesResponse extends AbstractWpOrgResponse
{
    /**
     * @param PageInfo $pageInfo
     * @param array<string,mixed> $themes   // TODO: use Collection<ThemeResponse>
     */
    public function __construct(
        public PageInfo $pageInfo,
        public array $themes,
    ) {}

    /** for API version 1.0 responses only -- do not use this otherwise! */
    public function toStdClass(): stdClass
    {
        return (object) ['info' => $this->pageInfo->toArray(), 'themes' => $this->themes];
    }
}
