<?php

namespace App\DTO;

use function Safe\json_decode;

class ApiResultsResponse implements \JsonSerializable
{
    /**
     * @var array<mixed> $results
     */
    private array $results;
    private int $page;
    private int $pages;
    private int $totalResults;
    private string $type;

    /**
     * Create a new instance to hold the API results.
     *
     * @param string $type
     * @param array<mixed> $results
     * @param int $page
     * @param int $perPage
     * @param int $total
     */
    public function __construct(string $type, array $results, int $page, int $perPage, int $total)
    {
        $this->type = $type;
        $this->results = $results;
        $this->totalResults = $total;
        $this->page = $page;
        $this->pages = intval(ceil($total / $perPage));
    }


    /**
     * Get the result data
     *
     * @return array<mixed>
     */
    public function getData()
    {
        return array_map(function ($record) {
            return json_decode($record->metadata);
        }, $this->results);
    }

    /**
     * Convert the object to a stdClass object.
     *
     * @return \stdClass
     */
    public function toStdClass(): \stdClass
    {
        $data = new \stdClass();
        $data->info = (object) $this->info();
        $data->{$this->type} = (object) $this->getData();  // or an array if needed

        return $data;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return [
            'info' => $this->info(),
            $this->type => $this->getData(),
        ];
    }

    /**
    * Info section to be used in the JSON output.
    *
    * @return array<string, int> Information about pagination and results
    */
    private function info(): array
    {
        return [
            'page' => $this->page,
            'pages' => $this->pages,
            'results' => $this->totalResults,
        ];
    }
}
