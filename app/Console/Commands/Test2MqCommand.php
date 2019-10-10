<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/3/28
 * Time: 下午3:04
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Lib\RabbitMq\TestRabbitQueue;

class Test2MqCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'rabbitMq:consume';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * queue name
     */
    const QUEUE_NAME = 'test2';

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    protected $rabbitMq;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->rabbitMq = new TestRabbitQueue(self::QUEUE_NAME);
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
        $this->consumeMq();
    }

    /**
     *  test / test2 监控同一交换器  执行不同的操作
     * 消费者
     */
    public function consumeMq(){

        $callback  = function ($message) {
            $data = json_decode($message->body,true);
            $data['type'] = 'test2';
            var_dump($data);
        };
        $this->rabbitMq->getOne($callback);
        $this->rabbitMq->startConsume();
    }
}