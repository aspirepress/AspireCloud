<?php

namespace App\Http\Requests\Plugins;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PluginInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
        ];
    }

    /**
     * Get the plugin slug from the request
     */
    public function getSlug(): ?string
    {
        return $this->query('slug');
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'Slug is required',
            ], 400),
        );
    }
}
