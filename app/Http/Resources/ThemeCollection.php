<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ThemeCollection extends ResourceCollection
{
    private int $page;
    private int $pages;
    private int $results;

    public function __construct($resource, int $page, int $pages, int $results)
    {
        parent::__construct($resource);
        $this->page = $page;
        $this->pages = $pages;
        $this->results = $results;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'info' => [
                'page' => $this->page,
                'pages' => $this->pages,
                'results' => $this->results,
            ],
            'themes' => $this->collection,
        ];
    }
}
