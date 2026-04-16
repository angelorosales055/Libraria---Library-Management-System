<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Transaction;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
| Run: php artisan schedule:work  (dev)  or add to cron in production
*/
Schedule::call(function () {
    // Mark overdue transactions daily
    $updated = Transaction::whereIn('status', ['active'])
        ->where('due_date', '<', today())
        ->update(['status' => 'overdue']);

    if ($updated > 0) {
        \Illuminate\Support\Facades\Log::info("Marked {$updated} transactions as overdue.");
    }
})->daily()->name('mark-overdue')->withoutOverlapping();
