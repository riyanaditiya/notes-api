<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: buat user, login, dan kembalikan header Authorization
     * supaya tidak perlu ulang-ulang di setiap test method.
     */
    protected function authHeader(): array
    {
        Auth::forgetGuards();
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user); 

        return ['Authorization' => "Bearer {$token}"];
    }

    /** @test */
    public function test_can_create_note(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/notes', [
                'title'   => 'Test Note',
                'content' => 'Test content',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'Success')
            ->assertJsonPath('message', 'Note created successfully')
            ->assertJsonPath('data.title', 'Test Note')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.is_favorite', false);

        $this->assertDatabaseHas('notes', [
            'title'   => 'Test Note',
            'content' => 'Test content',
        ]);
    }

    /** @test */
    public function test_cannot_create_note_without_title(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/notes', [
                'title'   => '',
                'content' => 'Test content',
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_can_read_notes(): void
    {
        $headers = $this->authHeader();

        $this->withHeaders($headers)->postJson('/api/notes', [
            'title'   => 'Note A',
            'content' => 'Content A',
        ]);

        $response = $this->withHeaders($headers)->getJson('/api/notes');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'Success')
            ->assertJsonPath('message', 'Notes retrieved successfully');

        $this->assertCount(1, $response->json('data.data'));
    }

    /** @test */
    public function test_can_read_single_note(): void
    {
        $headers = $this->authHeader();

        $create = $this->withHeaders($headers)->postJson('/api/notes', [
            'title'   => 'Note Detail',
            'content' => 'Content Detail',
        ]);

        $id = $create->json('data.id');

        $response = $this->withHeaders($headers)->getJson("/api/notes/{$id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.title', 'Note Detail');
    }

    /** @test */
    public function test_cannot_read_other_user_note(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $tokenA = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($userA);

        $create = $this->withHeaders(['Authorization' => "Bearer {$tokenA}"])
            ->postJson('/api/notes', [
                'title'   => 'Milik User A',
                'content' => 'Content',
            ]);

        $id = $create->json('data.id');

        $response = $this->actingAs($userB, 'api')->getJson("/api/notes/{$id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function test_can_update_note(): void
    {
        $headers = $this->authHeader();

        $create = $this->withHeaders($headers)->postJson('/api/notes', [
            'title'   => 'Old Title',
            'content' => 'Old content',
        ]);

        $id = $create->json('data.id');

        $response = $this->withHeaders($headers)->putJson("/api/notes/{$id}", [
            'title' => 'New Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'Success')
            ->assertJsonPath('message', 'Note updated successfully')
            ->assertJsonPath('data.title', 'New Title');

        $this->assertDatabaseHas('notes', [
            'id'    => $id,
            'title' => 'New Title',
        ]);
    }

    /** @test */
    public function test_can_delete_note(): void
    {
        $headers = $this->authHeader();

        $create = $this->withHeaders($headers)->postJson('/api/notes', [
            'title'   => 'To Delete',
            'content' => 'Content',
        ]);

        $id = $create->json('data.id');

        $response = $this->withHeaders($headers)->deleteJson("/api/notes/{$id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'Success')
            ->assertJsonPath('message', 'Note deleted successfully');

        // Soft delete: data masih ada di database, tapi deleted_at terisi
        $this->assertSoftDeleted('notes', ['id' => $id]);
    }

    /** @test */
    public function test_deleted_note_not_visible_in_list(): void
    {
        $headers = $this->authHeader();

        $create = $this->withHeaders($headers)->postJson('/api/notes', [
            'title'   => 'Akan Dihapus',
            'content' => 'Content',
        ]);

        $id = $create->json('data.id');

        $this->withHeaders($headers)->deleteJson("/api/notes/{$id}");

        $response = $this->withHeaders($headers)->getJson('/api/notes');

        $response->assertJsonMissing(['id' => $id]);
    }

    /** @test */
    public function test_cannot_access_notes_without_token(): void
    {
        $response = $this->getJson('/api/notes');

        $response->assertStatus(401);
    }
}