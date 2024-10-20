<?php

namespace App\Models;

class ApiResultsResponse implements \JsonSerializable
{
    protected $results;
    protected $page;
    protected $pages;
    protected $totalResults;
    protected $type;

    public function __construct(string $type, array $results, int $page, int $perPage, int $total)
    {
        $this->type = $type;
        $this->results = $results;
        $this->totalResults = $total;
        $this->page = $page;
        $this->pages = ceil($total / $perPage);
    }

    // Info section to be used in the JSON output
    public function info(): array
    {
        return [
            'page' => $this->page,
            'pages' => $this->pages,
            'results' => $this->totalResults,
        ];
    }

    public function getData()
    {
        return array_map(function ($record) {
            return json_decode($record->metadata);
        }, $this->results);
    }

    public function toStdClass(): \stdClass
    {
        $data = new \stdClass();
        $data->info = (object) $this->info();
        $data->{$this->type} = (object) $this->getData();  // or an array if needed

        return $data;
    }
    public function jsonSerialize(): mixed
    {
        return [
            'info' => $this->info(),
            $this->type => $this->getData(),
        ];
    }
}
