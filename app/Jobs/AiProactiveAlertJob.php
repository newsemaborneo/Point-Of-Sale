<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Ai\AiProactiveAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class AiProactiveAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(AiProactiveAlertService $alertService): void
    {
        // Run for all admin and supervisor users
        $users = User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'supervisor']))->get();

        foreach ($users as $user) {
            $alerts = $alertService->getAlerts($user);

            if (!empty($alerts)) {
                // Cache alerts per user for 1 hour (frontend polls this)
                Cache::put("ai_alerts_user_{$user->id}", $alerts, now()->addHour());
            }
        }
    }
}
