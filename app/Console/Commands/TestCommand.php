<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/3/28
 * Time: 下午3:04
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lib\RabbitMq\RabbitMqBase;

class TestCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'rabbitMq:publish';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        $configs = array('host'=>'127.0.0.1','port'=>5672,'username'=>'asdf','password'=>'123456','vhost'=>'/');
        $exchange_name = 'class-e-1';
        $queue_name = 'class-q-1';
        $route_key = 'class-r-1';
        $ra = new RabbitMqBase($configs,$exchange_name,$queue_name,$route_key);
        for($i=0;$i<=100;$i++){
            $ra->send(date('Y-m-d H:i:s',time()));
        }
    }
}