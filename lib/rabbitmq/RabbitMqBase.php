<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/3/28
 * Time: 下午2:51
 */
namespace Lib\RabbitMq;


use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMqBase {

    private $_conn;

    private function connection() {
        $this->_conn = new AMQPStreamConnection();
        $channel = $this->_conn->channel();
        $channel->queue_declare('hello', false, false, false, false);
    }
}