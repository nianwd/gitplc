<?php

namespace App\Console;

use App\Console\Commands\SetInvestOrder;
use App\Console\Commands\SetInvestOverflow;
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
        SetInvestOrder::class,
        SetInvestOverflow::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //每天12点结算理财订单
        $schedule->command('setInvestOrder')->dailyAt('12:00')->withoutOverlapping()->runInBackground();
        //每天13点计算团队溢出
        $schedule->command('setInvestOverflow')->dailyAt('13:00')->withoutOverlapping()->runInBackground();

        // $schedule->command('inspire')
        //          ->hourly();
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
