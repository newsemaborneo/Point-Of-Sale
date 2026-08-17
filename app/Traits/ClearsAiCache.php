<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

trait ClearsAiCache
{
    public static function bootClearsAiCache()
    {
        static::saved(function () {
            self::clearAiCache();
        });

        static::deleted(function () {
            self::clearAiCache();
        });
    }

    public static function clearAiCache()
    {
        try {
            $userIds = User::pluck('id');
            foreach ($userIds as $id) {
                Cache::forget("ai_insights_user_{$id}");
                Cache::forget("ai_recommendations_user_{$id}");
            }
        } catch (\Exception $e) {
            // Avoid failing database transactions if query fails
        }
    }
}
