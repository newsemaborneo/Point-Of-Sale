<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\Ai\DashboardInsightService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardInsightServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_admin_intelligence_for_admin(): void
    {
        $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        $user = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin Sistem',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $service = $this->app->make(DashboardInsightService::class);

        $title = $service->getAiCenterTitleForUser($user);
        $insights = $service->getInsightsForUser($user);
        $recommendations = $service->getRecommendationsForUser($user);
        $chat = $service->getChatMessagesForUser($user);

        $this->assertEquals('SuperAdmin Intelligence', $title);
        // Default admin insight references Bandung (from mock or fallback)
        $this->assertNotEmpty($insights);
        $this->assertNotEmpty($recommendations);
        $this->assertStringContainsString('Admin Sistem', $chat[0]['text']);
    }

    public function test_it_returns_supervisor_intelligence_for_supervisor(): void
    {
        $supervisorRole = Role::create(['name' => 'Supervisor', 'slug' => 'supervisor']);
        $branch = Branch::create(['name' => 'East Branch', 'code' => 'BR-EAST']);
        $user = User::create([
            'role_id' => $supervisorRole->id,
            'branch_id' => $branch->id,
            'name' => 'Supervisor Timur',
            'email' => 'supervisor@example.com',
            'password' => bcrypt('password'),
        ]);

        $service = $this->app->make(DashboardInsightService::class);

        $title = $service->getAiCenterTitleForUser($user);
        $insights = $service->getInsightsForUser($user);
        $recommendations = $service->getRecommendationsForUser($user);
        $chat = $service->getChatMessagesForUser($user);

        $this->assertEquals('Supervisor Intelligence', $title);
        $this->assertNotEmpty($insights);
        $this->assertNotEmpty($recommendations);
        $this->assertStringContainsString('East Branch', $chat[0]['text']);
    }
}
