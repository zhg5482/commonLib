<?php
namespace App\Lib\RabbitMq;

use Illuminate\Support\Facades\Config;

class TestRabbitQueue extends RabbitQueueBase
{
    public function __construct($queueName)
    {
        parent::__construct($queueName);
    }

    public function getServerConfig()
    {
        return Config::get('queue.rabbitmq');
    }
}