<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $stores = config('cache.stores', []);
        if (!isset($stores['database'])) {
            config([
                'cache.stores.database' => [
                    'driver' => 'database',
                    'table' => 'cache',
                    'connection' => null,
                    'lock_connection' => null,
                ],
            ]);
        }
    }

    public function boot()
    {
        try {
            if (DB::connection()->getDriverName() === 'sqlite') {
                $busyTimeout = (int) env('SQLITE_BUSY_TIMEOUT', 5000);
                DB::statement('PRAGMA journal_mode = WAL');
                DB::statement('PRAGMA synchronous = NORMAL');
                DB::statement('PRAGMA busy_timeout = ' . $busyTimeout);
            }
        } catch (\Throwable $e) {
            // Keep app running even if pragmas fail (e.g., during install).
        }
    }
}
