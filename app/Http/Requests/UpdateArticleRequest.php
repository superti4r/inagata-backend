<?php

declare(strict_types=1);

namespace App\Http\Requests;

class UpdateArticleRequest extends APIRequest
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
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'author' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }
}
