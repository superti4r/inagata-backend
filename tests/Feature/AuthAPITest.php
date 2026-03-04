<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAPITest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER_ENDPOINT = '/api/register';

    private const USER_EMAIL = 'john@example.com';

    private const USER_PASSWORD = 'password';

    public function test_user_can_register_and_receive_sanctum_token(): void
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'John Doe',
            'email' => self::USER_EMAIL,
            'password' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Register successful.')
            ->assertJsonPath('data.user.name', 'John Doe')
            ->assertJsonPath('data.user.email', self::USER_EMAIL)
            ->assertJsonPath('data.user.role', 'user');

        $this->assertNotEmpty($response->json('data.token'));

        $this->assertDatabaseHas('users', [
            'email' => self::USER_EMAIL,
            'role' => 'user',
        ]);
    }

    public function test_register_validation_errors_are_formatted_consistently(): void
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['name', 'email', 'password'],
            ]);
    }

    public function test_register_from_swagger_style_request_does_not_require_csrf_token(): void
    {
        $response = $this
            ->withHeaders([
                'Origin' => 'http://127.0.0.1:8000',
                'Referer' => 'http://127.0.0.1:8000/api/documentation',
                'X-CSRF-TOKEN' => '',
            ])
            ->postJson(self::REGISTER_ENDPOINT, [
                'name' => 'Swagger User',
                'email' => 'swagger.user@example.com',
                'password' => 'secret123',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Register successful.');
    }

    public function test_user_can_login_and_receive_sanctum_token(): void
    {
        User::factory()->create([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
            'role' => 'user',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('data.user.email', self::USER_EMAIL);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => self::USER_EMAIL,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_authenticated_user_can_logout_and_revoke_current_token(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logout successful.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated.');
    }
}
