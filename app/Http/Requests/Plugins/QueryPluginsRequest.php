<?php

namespace App\Http\Requests\Plugins;

use Illuminate\Foundation\Http\FormRequest;

class QueryPluginsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
    * Get the validation rules that apply to the request.
    *
    * @return array<string, array<int, mixed>>
    */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string'],
            'tag' => ['sometimes', 'string'],
            'author' => ['sometimes', 'string'],
            'browse' => ['sometimes', 'string', 'in:new,updated,top-rated,popular'],
        ];
    }

    public function getPage(): int
    {
        return max(1, (int) $this->query('page', '1'));
    }

    public function getPerPage(): int
    {
        return (int) $this->query('per_page', '24');
    }

    public function getBrowse(): string
    {
        return $this->query('browse', 'popular');
    }
}
