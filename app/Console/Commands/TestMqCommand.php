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

class TestMqCommand extends Command
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
     * queue name
     */
    const QUEUE_NAME = 'test';

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
     * 消费者
     */
    public function consumeMq(){

        $callback  = function ($message) {
            $data = json_decode($message->body,true);
            //$this->rabbitMq::ack($message);  //是否确认消息
            var_dump($data);
        };
        $this->rabbitMq->getOne($callback);
        $this->rabbitMq->startConsume();
    }

    /**
     * 生产者 消息确认
     */
    public function publishMq() {
        try{
            $this->rabbitMq->confirm();//开启发送确认方式
            $message = array(
                '__table' => 'account_record',
                'post_id' => 2323,
                'city_id' => 17,
                'bid_amount' => 3000,
                'cash_amount' => 1800,
                'coupon_amount' => 1200
            );
            $this->rabbitMq->addOne($message);
            $this->rabbitMq->wait_for_pending_acks(); //阻塞等待消息确认
        } catch (\Exception $e) {
            var_export($e->getMessage(),true);
        }

    }
}