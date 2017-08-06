<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ClearHistory::class,
        Commands\StoreRates::class,
        Commands\Alert::class,
        Commands\RefreshCache::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('rates:store')
                  ->cron('0 */4 * * *');
         $schedule->command('rates:refresh')
                  ->cron('/30 * * * *');
    //     $schedule->command('rates:alert')
    //              ->cron('10 * * * * ');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');

    }
}
