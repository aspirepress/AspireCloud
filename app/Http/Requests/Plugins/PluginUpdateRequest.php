<?php

namespace App\Http\Requests\Plugins;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

use function Safe\json_decode;

class PluginUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'plugins' => ['required', 'string', 'json'],
            'translations' => ['required', 'string'],
            'locale' => ['required', 'string'],
            'all' => ['sometimes', 'string', 'in:true,false'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->failed()) {
                return;
            }

            $plugins = json_decode($this->plugins, true);
            if (!isset($plugins['plugins']) || !is_array($plugins['plugins'])) {
                $validator->errors()->add(
                    'plugins',
                    'The plugins JSON must contain a "plugins" array.',
                );
            }
        });
    }
}
