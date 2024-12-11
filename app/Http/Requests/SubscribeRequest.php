<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;  // all users allowed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'urls' => 'required|array|min:1',
            'urls.*' => 'required|url|distinct', // Each URL must be a valid and unique URL
        ];
    }

    protected function prepareForValidation()
    {
        // Transform the `urls` string into an array by splitting on newlines, remove whitespaces
        $this->merge([
            'urls' => array_filter(
                array_map('trim',
                    explode("\n", $this->input('urls'))
                )
            ),
        ]);
    }

    public function messages(): array
    {
        return [
            'urls.required' => 'You must provide at least one URL.',
            'urls.*.url' => 'Each URL must be a valid link.',
            'urls.*.distinct' => 'Duplicate URLs are not allowed.',
        ];
    }
}
