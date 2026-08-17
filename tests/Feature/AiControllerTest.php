<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $supervisorUser;
    protected User $cashierUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        $supervisorRole = Role::create(['name' => 'Supervisor', 'slug' => 'supervisor']);
        $cashierRole = Role::create(['name' => 'Kasir', 'slug' => 'cashier']);

        $branch = Branch::create(['name' => 'Central Branch', 'code' => 'BR-CENTRAL']);

        $this->adminUser = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin Sistem',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->supervisorUser = User::create([
            'role_id' => $supervisorRole->id,
            'branch_id' => $branch->id,
            'name' => 'Supervisor Satu',
            'email' => 'supervisor@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->cashierUser = User::create([
            'role_id' => $cashierRole->id,
            'branch_id' => $branch->id,
            'name' => 'Kasir Satu',
            'email' => 'kasir@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_it_denies_access_to_unauthenticated_users(): void
    {
        $response = $this->postJson(route('ai.chat'), ['message' => 'Halo AI']);
        $response->assertStatus(401);
    }

    public function test_it_denies_access_to_unauthorized_roles(): void
    {
        $response = $this->actingAs($this->cashierUser)
            ->postJson(route('ai.chat'), ['message' => 'Halo AI']);
            
        $response->assertStatus(403);
    }

    public function test_it_allows_admin_to_chat_and_returns_fallback_response(): void
    {
        // Force API key to be null for fallback testing
        putenv('GEMINI_API_KEY=');

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('ai.chat'), ['message' => 'Berapa omset hari ini?']);

        $response->assertStatus(200)
            ->assertJsonStructure(['role', 'text'])
            ->assertJson([
                'role' => 'assistant'
            ]);

        $this->assertStringContainsString('ringkasan penjualan', $response->json('text'));
    }

    public function test_it_allows_supervisor_to_chat_and_returns_fallback_response(): void
    {
        putenv('GEMINI_API_KEY=');

        $response = $this->actingAs($this->supervisorUser)
            ->postJson(route('ai.chat'), ['message' => 'Bagaimana stok produk?']);

        $response->assertStatus(200)
            ->assertJsonStructure(['role', 'text']);

        $this->assertStringContainsString('stok', strtolower($response->json('text')));
    }

    public function test_it_validates_required_chat_message(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('ai.chat'), ['message' => '']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('message');
    }

    public function test_it_allows_admin_to_fetch_dashboard_data_async(): void
    {
        putenv('GEMINI_API_KEY=');

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('ai.dashboard-data'));

        $response->assertStatus(200)
            ->assertJsonStructure(['insights', 'recommendations']);
    }

    public function test_it_allows_supervisor_to_fetch_dashboard_data_async(): void
    {
        putenv('GEMINI_API_KEY=');

        $response = $this->actingAs($this->supervisorUser)
            ->getJson(route('ai.dashboard-data'));

        $response->assertStatus(200)
            ->assertJsonStructure(['insights', 'recommendations']);
    }
}
