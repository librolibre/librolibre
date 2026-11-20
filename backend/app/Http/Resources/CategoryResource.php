<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A category or genre (Category in the API contract): only code and name, no id or timestamps.
 * Books refer to categories by code, like languages.
 *
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example novel */
            'code' => $this->code,
            /** @example Novela */
            'name' => $this->name,
        ];
    }
}
