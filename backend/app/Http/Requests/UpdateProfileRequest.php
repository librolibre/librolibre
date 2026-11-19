<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // auth:sanctum already guarantees a logged-in user; users can always edit their own profile
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['sometimes', 'string', 'min:2', 'max:60'],
            'description' => ['nullable', 'string', 'max:300'],
            'languages' => ['sometimes', 'array'],
            // An unknown language code returns 422
            'languages.*' => ['string', 'min:2', 'max:2', Rule::exists('languages', 'code')],
            'location' => ['nullable', 'array'],
            'location.latitude' => ['required_with:location', 'numeric'],
            'location.longitude' => ['required_with:location', 'numeric'],
            'postal_code' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
        ];
    }
}
