<?php

namespace App\Http\Requests;

use App\Rules\ValidEmailDomain;
use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    protected const VALID_ORIGIN = 'www.olx.ua';  // maybe use config for this

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
            'email' => ['required', 'email', new ValidEmailDomain()],
            'urls' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
                foreach ($value as $url) {
                    $parsedUrl = parse_url($url);
                    if (!isset($parsedUrl['host']) || $parsedUrl['host'] !== self::VALID_ORIGIN) {
                        $fail("The URL '{$url}' is not from the valid origin.");
                    }
                }
            }],
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

        /*$validUrls = array_filter($this->input('urls'), function ($url) {
            $parsedUrl = parse_url($url);
            return isset($parsedUrl['host']) && $parsedUrl['host'] === self::VALID_ORIGIN;
        });

        // Replace with valid URLs only
        $this->merge(['urls' => $validUrls,]);*/
    }

    public function messages(): array
    {
        return [
            'urls.required' => 'You must provide at least one URL (origin should be from `' . self::VALID_ORIGIN . '`).',
            'urls.*.url' => 'Each URL must be a valid link.',
            'urls.*.distinct' => 'Duplicate URLs are not allowed.',
        ];
    }
}
