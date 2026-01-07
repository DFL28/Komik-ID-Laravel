<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
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
