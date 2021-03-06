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
        Commands\GetCryptoComment::class,
        Commands\GetTwitterAccount::class,
        Commands\UpdateTwitterUserdata::class,
        Commands\TwitterAutoFollow::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('command:getcryptocomment')->everyFiveMinutes();
         $schedule->command('command:getnewaccount')->dailyAt('02:20');
         $schedule->command('command:updateaccount')->dailyAt('04:20');
         $schedule->command('command:twitterautofollow')->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
