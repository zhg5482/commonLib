<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/3/28
 * Time: 下午2:51
 */
namespace App\Lib\RabbitMq;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitQueueBase
{
    public $channel        = "";
    protected $connection     = "";
    protected $queueKey       = "";
    protected $exchange       = "";
    protected $queueConf      = [];
    protected $arrCurrentConf = [];
    protected $consumerTag    = "";
    protected $_durable        = false;
    protected $_exchange_durable = false;
    protected $_no_ack = true;
    protected $_model = 'fanout';

    public function __construct($queueName)
    {
        if ( method_exists($this, 'getServerConfig') ) {
            $this->queueConf  = $this->getServerConfig();
        } else {
            throw new \Exception("Func getServerConfig is needed", 1);
        }
        $this->queueKey   = $this->queueConf['info'][$queueName]['queueKey'];
        $this->exchange   = $this->queueConf['info'][$queueName]['exchange'];
        if (isset($this->queueConf['info'][$queueName]['durable'])) {
            $this->_durable = $this->queueConf['info'][$queueName]['durable'];
        }
        if(isset($this->queueConf['info'][$queueName]['exchange_durable'])){
            $this->_exchange_durable = $this->queueConf['info'][$queueName]['exchange_durable'];
        }else{
            $this->_exchange_durable = $this->_durable;
        }
        if(isset($this->queueConf['info'][$queueName]['no_ack'])){
            $this->_no_ack = $this->queueConf['info'][$queueName]['no_ack'];
        }
        if(isset($this->queueConf['info'][$queueName]['model'])){
            $this->_no_ack = $this->queueConf['info'][$queueName]['model'];
        }
        $this->getRandServer();
        $this->connection();
    }

    /**
     * connection
     */
    public function connection()
    {
        $connection = new AMQPStreamConnection(
            $this->arrCurrentConf['host'],
            $this->arrCurrentConf['port'],
            $this->arrCurrentConf['user'],
            $this->arrCurrentConf['pass'],
            $this->arrCurrentConf['vhost']
        );
        $this->connection = $connection;
        $this->channel = $connection->channel();
        $this->channel->exchange_declare($this->exchange, $this->_model, false, $this->_exchange_durable, false);  //声明交换器
        $this->channel->queue_declare($this->queueKey, true, $this->_durable, false, false); //声明队列
        $this->channel->basic_qos(null, 1, true);  //设置同事最多处理 1 条请求  在 no_ack == false 下生效  在未接受应答确认之前 不会分发新的消息给消费者
        $this->channel->queue_bind($this->queueKey, $this->exchange); //绑定队列到交换器上
    }

    /**
     * 发布消息
     * @param $arrContent
     * @return mixed
     */
    public function addOne($arrContent)
    {
        $messageBody = json_encode($arrContent);
        $message = new AMQPMessage(
            $messageBody,
            ['content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
        return $this->channel->basic_publish($message, $this->exchange);
    }

    /**
     * 获取单条消息
     * @param $funcCallback
     */
    public function getOne($funcCallback)
    {
        $this->channel->basic_consume($this->queueKey, $this->consumerTag, false, $this->_no_ack, false, false, $funcCallback);
    }

    /**
     * 获得当前队列积压消息数
     * @return mixed
     */
    public function getQueueReadyCount()
    {
        return $this->channel->queue_declare($this->queueKey, false, $this->_durable, false, false);
    }

    /**
     *  消费消息
     *  consume
     */
    public function startConsume()
    {
        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     * 随机选择节点
     * RandServe
     */
    public function getRandServer()
    {
        if (!$this->queueConf) {
            $this->queueConf  = $this->getServerConfig();
        }
        $serverInFos = $this->queueConf['server'];
        shuffle($serverInFos);
        $this->arrCurrentConf  = $serverInFos[0];
    }

    /**
     * 消费者消息确认
     * @param $message
     */
    public static function ack($message)
    {
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    }

    /**
     * 消费者消息未确认
     * @param $message
     */
    public static function nack($message)
    {
        $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);
    }

    /**
     * 开启生产者消息确认模式
     * publish confirm
     */
    public function confirm()
    {
        $this->channel->confirm_select();
    }

    /**
     * 阻塞等待消息确认
     * wait_for_pending_acks
     */
    public function wait_for_pending_acks()
    {
        $this->channel->wait_for_pending_acks();
    }

    /**
     * wait_for_pending_acks_returns
     */
    public function wait_for_pending_acks_returns()
    {
        $this->channel->wait_for_pending_acks_returns();
    }

    /**
     * close
     */
    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }

}
