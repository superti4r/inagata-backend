<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class UpdateArticleDTO
{
    public function __construct(
        public string $title,
        public string $content,
        public string $author,
        public int $categoryId,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            title: (string) $validated['title'],
            content: (string) $validated['content'],
            author: (string) $validated['author'],
            categoryId: (int) $validated['category_id'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'author' => $this->author,
            'category_id' => $this->categoryId,
        ];
    }
}
