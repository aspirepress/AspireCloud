<?php

namespace App\Http\Resources\Plugins;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PluginUpdateCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->all();
    }
}
