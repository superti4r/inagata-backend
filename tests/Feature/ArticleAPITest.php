<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArticleAPITest extends TestCase
{
    use RefreshDatabase;

    private const ARTICLES_ENDPOINT = '/api/articles';

    private const ARTICLES_SEARCH_ENDPOINT = '/api/articles/search';

    private const ARTICLE_DETAIL_ENDPOINT = '/api/articles/';

    private const CATEGORY_TECH = 'Technology';

    private const ARTICLE_TITLE = 'Laravel Best Practice';

    private const ARTICLE_CONTENT = 'Content body';

    private const ARTICLE_AUTHOR = 'Jane Doe';

    private const UPDATED_TITLE = 'New title';

    public function test_public_user_can_get_paginated_articles_with_default_limit(): void
    {
        $category = Category::query()->create(['name' => self::CATEGORY_TECH]);
        $author = User::factory()->create();

        for ($index = 1; $index <= 12; $index++) {
            Article::query()->create([
                'title' => 'Article '.$index,
                'content' => 'Content '.$index,
                'author' => 'Author '.$index,
                'category_id' => $category->id,
                'user_id' => $author->id,
            ]);
        }

        $response = $this->getJson(self::ARTICLES_ENDPOINT);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pagination.page', 1)
            ->assertJsonPath('data.pagination.limit', 10)
            ->assertJsonPath('data.pagination.total', 12);

        $this->assertCount(10, $response->json('data.items'));
    }

    public function test_public_user_can_get_article_detail(): void
    {
        $category = Category::query()->create(['name' => self::CATEGORY_TECH]);
        $author = User::factory()->create();

        $article = Article::query()->create([
            'title' => 'Laravel Clean API',
            'content' => 'Long content',
            'author' => 'John Doe',
            'category_id' => $category->id,
            'user_id' => $author->id,
        ]);

        $response = $this->getJson(self::ARTICLE_DETAIL_ENDPOINT.$article->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $article->id)
            ->assertJsonPath('data.title', 'Laravel Clean API')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.user.id', $author->id);
    }

    public function test_get_article_detail_returns_not_found_when_missing(): void
    {
        $response = $this->getJson(self::ARTICLE_DETAIL_ENDPOINT.'999999');

        $response->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Article not found.');
    }

    public function test_admin_can_create_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create(['name' => self::CATEGORY_TECH]);

        Sanctum::actingAs($admin);

        $payload = [
            'title' => self::ARTICLE_TITLE,
            'content' => self::ARTICLE_CONTENT,
            'author' => self::ARTICLE_AUTHOR,
            'category_id' => $category->id,
        ];

        $response = $this->postJson(self::ARTICLES_ENDPOINT, $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', self::ARTICLE_TITLE)
            ->assertJsonPath('data.user_id', $admin->id)
            ->assertJsonPath('data.category_id', $category->id);

        $this->assertDatabaseHas('articles', [
            'title' => self::ARTICLE_TITLE,
            'user_id' => $admin->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_non_admin_cannot_create_article(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::query()->create(['name' => self::CATEGORY_TECH]);

        Sanctum::actingAs($user);

        $response = $this->postJson(self::ARTICLES_ENDPOINT, [
            'title' => self::ARTICLE_TITLE,
            'content' => self::ARTICLE_CONTENT,
            'author' => self::ARTICLE_AUTHOR,
            'category_id' => $category->id,
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden.');
    }

    public function test_guest_cannot_create_article(): void
    {
        $category = Category::query()->create(['name' => self::CATEGORY_TECH]);

        $response = $this->postJson(self::ARTICLES_ENDPOINT, [
            'title' => self::ARTICLE_TITLE,
            'content' => self::ARTICLE_CONTENT,
            'author' => self::ARTICLE_AUTHOR,
            'category_id' => $category->id,
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_create_article_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($admin);

        $response = $this->postJson(self::ARTICLES_ENDPOINT, []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['title', 'content', 'author', 'category_id'],
            ]);
    }

    public function test_admin_can_update_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $categoryA = Category::query()->create(['name' => self::CATEGORY_TECH]);
        $categoryB = Category::query()->create(['name' => 'Business']);

        $article = Article::query()->create([
            'title' => 'Old title',
            'content' => 'Old content',
            'author' => 'Old author',
            'category_id' => $categoryA->id,
            'user_id' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson(self::ARTICLE_DETAIL_ENDPOINT.$article->id, [
            'title' => self::UPDATED_TITLE,
            'content' => 'New content',
            'author' => 'New author',
            'category_id' => $categoryB->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', self::UPDATED_TITLE)
            ->assertJsonPath('data.category_id', $categoryB->id);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => self::UPDATED_TITLE,
            'category_id' => $categoryB->id,
        ]);
    }

    public function test_non_admin_cannot_update_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::query()->create(['name' => self::CATEGORY_TECH]);

        $article = Article::query()->create([
            'title' => 'Old title',
            'content' => 'Old content',
            'author' => 'Old author',
            'category_id' => $category->id,
            'user_id' => $admin->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(self::ARTICLE_DETAIL_ENDPOINT.$article->id, [
            'title' => self::UPDATED_TITLE,
            'content' => 'New content',
            'author' => 'New author',
            'category_id' => $category->id,
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden.');
    }

    public function test_admin_can_delete_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create(['name' => self::CATEGORY_TECH]);

        $article = Article::query()->create([
            'title' => 'Delete me',
            'content' => 'Delete content',
            'author' => 'Admin',
            'category_id' => $category->id,
            'user_id' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson(self::ARTICLE_DETAIL_ENDPOINT.$article->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Article deleted successfully.');

        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_non_admin_cannot_delete_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::query()->create(['name' => self::CATEGORY_TECH]);

        $article = Article::query()->create([
            'title' => 'Delete me',
            'content' => 'Delete content',
            'author' => 'Admin',
            'category_id' => $category->id,
            'user_id' => $admin->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(self::ARTICLE_DETAIL_ENDPOINT.$article->id);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden.');
    }

    public function test_search_articles_can_filter_by_category_and_keyword(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tech = Category::query()->create(['name' => self::CATEGORY_TECH]);
        $biz = Category::query()->create(['name' => 'Business']);

        Article::query()->create([
            'title' => 'Laravel Guide',
            'content' => 'A',
            'author' => 'John',
            'category_id' => $tech->id,
            'user_id' => $admin->id,
        ]);

        Article::query()->create([
            'title' => 'PHP Basics',
            'content' => 'B',
            'author' => 'John',
            'category_id' => $tech->id,
            'user_id' => $admin->id,
        ]);

        Article::query()->create([
            'title' => 'Laravel Business Strategy',
            'content' => 'C',
            'author' => 'John',
            'category_id' => $biz->id,
            'user_id' => $admin->id,
        ]);

        $response = $this->getJson(self::ARTICLES_SEARCH_ENDPOINT.'?category_id='.$tech->id.'&keyword=Laravel');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pagination.total', 1);

        $this->assertCount(1, $response->json('data.items'));
        $this->assertSame('Laravel Guide', $response->json('data.items.0.title'));
    }

    public function test_search_articles_supports_page_and_limit_parameters(): void
    {
        $category = Category::query()->create(['name' => self::CATEGORY_TECH]);
        $author = User::factory()->create(['role' => 'admin']);

        for ($index = 1; $index <= 3; $index++) {
            Article::query()->create([
                'title' => 'Laravel '.$index,
                'content' => 'Content '.$index,
                'author' => 'Author '.$index,
                'category_id' => $category->id,
                'user_id' => $author->id,
            ]);
        }

        $response = $this->getJson(self::ARTICLES_SEARCH_ENDPOINT.'?keyword=Laravel&page=2&limit=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pagination.page', 2)
            ->assertJsonPath('data.pagination.limit', 1)
            ->assertJsonPath('data.pagination.total', 3);

        $this->assertCount(1, $response->json('data.items'));
    }
}
