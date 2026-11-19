<?php

namespace App\Http\Resources;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A language (Language in the API contract): only code and name, no id or timestamps.
 *
 * @mixin Language
 */
class LanguageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example es */
            'code' => $this->code,
            /** @example Español */
            'name' => $this->name,
        ];
    }
}
