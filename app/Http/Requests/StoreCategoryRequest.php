<?php

declare(strict_types=1);

namespace App\Http\Requests;

class StoreCategoryRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
        ];
    }
}
