<?php

namespace App\Console;

use App\Console\Commands\Test2MqCommand;
use App\Console\Commands\TestMqCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 注：test / test2 验证 消息投递模式 fanout / 消息确认  ack
        TestMqCommand::class,
        Test2MqCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
