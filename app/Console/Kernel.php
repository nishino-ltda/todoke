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
        Commands\ResetCommand::class,
        Commands\ScheduleVotingRoundsCommand::class,
        Commands\ProcessExpiredVotingRoundsCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule monthly voting rounds on the 1st day of each month
        $schedule->command('voting:schedule-rounds')
            ->monthlyOn(1, '00:01')
            ->appendOutputTo(storage_path('logs/voting-schedule.log'));
        
        // Process expired voting rounds daily
        $schedule->command('voting:process-expired')
            ->daily()
            ->appendOutputTo(storage_path('logs/voting-process.log'));
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
