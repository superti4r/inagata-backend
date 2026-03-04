<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryAPITest extends TestCase
{
    use RefreshDatabase;

    private const CATEGORIES_ENDPOINT = '/api/categories';

    private const CATEGORY_TECH = 'Technology';

    public function test_public_user_can_get_categories(): void
    {
        Category::query()->create(['name' => self::CATEGORY_TECH]);
        Category::query()->create(['name' => 'Business']);

        $response = $this->getJson(self::CATEGORIES_ENDPOINT);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Categories retrieved successfully.');

        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($admin);

        $response = $this->postJson(self::CATEGORIES_ENDPOINT, [
            'name' => self::CATEGORY_TECH,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Category created successfully.')
            ->assertJsonPath('data.name', self::CATEGORY_TECH);

        $this->assertDatabaseHas('categories', [
            'name' => self::CATEGORY_TECH,
        ]);
    }

    public function test_non_admin_cannot_create_category(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($user);

        $response = $this->postJson(self::CATEGORIES_ENDPOINT, [
            'name' => self::CATEGORY_TECH,
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden.');
    }

    public function test_guest_cannot_create_category(): void
    {
        $response = $this->postJson(self::CATEGORIES_ENDPOINT, [
            'name' => self::CATEGORY_TECH,
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_create_category_validates_required_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($admin);

        $response = $this->postJson(self::CATEGORIES_ENDPOINT, []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['name'],
            ]);
    }
}
