<?php

namespace Lib\Cache;

/**
 * redis缓存类
 * Class Redis
 * @package Lib\Cache
 */
class Redis extends BaseCache
{
    /**
     * redis服务器配置信息
     * @var array 每个数组元素包括ip、端口和权重
     */
    private $servers = array();

    /**
     * redis实例
     * @var Redis
     */
    private $redisObj = null;

    /**
     * 键的前缀，用于区分业务
     * @var string
     */
    private $prefix = "";

    /**
     * 初始化memached，将memached集群加到连接池，并启用一致性hash
     * @param array  $servers      类似:$servers = array(array('host'=>'127.0.0.1','port'=>11211,'weight'=>1,'password' => '', 'timeout' => 3), array('host'=>'127.0.0.1','port'=>11212,'weight'=>1,'password' => '', 'timeout' => 3))
     * @param string $prefix       对于各个公用缓存的数据，以其平台标示作为命名空间，隔离不同平台的数据，
     * @throws Exception
     */
    public function __construct(array $servers, $namespace = "")
    {

        if (!is_array($servers) || empty($servers)) {
            throw new \Exception("缓存服务器连接信息不能为空！");
            exit;
        } else {
            foreach ($servers as $row) {
                if (!is_array($row) || !isset($row['host']) || !isset($row['port'])) {// || !isset($row['weight'])
                    throw new \Exception("Redis缓存服务器连接信息不能为空！");
                    exit;
                }
            }
        }

        $this->servers = $servers;
        if(is_string($namespace)){
            $this->prefix  = $namespace;
        }
        $this->redisObj = new \redis();
        $this->redisObj->connect($servers[0]['host'] , $servers[0]['port'], $servers[0]['timeout']);
        if (isset($servers[0]['password']) && !empty($servers[0]['password'])) {
            $this->redisObj->auth($servers[0]['password']);
        }
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method,$args)
    {
        return $this->redisObj->$method($args[0],$args[1],$args[2]);
    }

    /**
     * 设置缓存条目
     * @param string $key    缓存的键
     * @param mixed  $val   缓存的值
     * @return int      设置缓存是否成功
     */
    public function set($key = '', $val = null, $expire = 0)
    {
        $key = $this->prefix . $key;
        $value = json_encode($val);
        $ret = $this->redisObj->set($key, $value);
        if($expire > 0){
            $ret = $this->redisObj->expire($key, $expire);
        }
        return $ret;
    }

    /**
     * 获取单条缓存条目
     * @param string $key 缓存数据的键
     * @return string or false 缓存
     */
    public function get($key = "")
    {
        $key = $this->prefix . $key;
        $value = $this->redisObj->get($key);
        return json_decode($value, true);
    }

    /**
     * 删除单条缓存条目
     * @param string $key 缓存数据的键
     * @return string or false 缓存
     */
    public function delete($key = "")
    {
        $key = $this->prefix . $key;
        $value = $this->redisObj->delete($key);
        return json_decode($value, true);
    }

    /**
     * 添加到队列
     * @param string $key 键名
     * @param mixed  $val 键值
     * @return int
     */
    public function lPush($key , $val) {
        $key = $this->prefix . $key;
        $value = json_encode($val);

        return $this->redisObj->lPush($key ,$value);
    }

    /**
     * 从队列获取数据
     * @param string $key 键名
     * @param mixed  $val 键值
     * @return boolean
     */
    public function rPop($key) {
        $key = $this->prefix . $key;

        $value = $this->redisObj->rPop($key);
        return json_decode($value, true);
    }

    /**
     * redis事务开启
     */
    public function multi() {
        $this->redisObj->multi();
    }

    /**
     * redis事务关闭
     */
    public function exec() {
        return $this->redisObj->exec();
    }

    /**
     * 返回存储在key对应的有序集合中的元素的个数
     * @param string $key 键名
     * @return int
     */
    public function zCard($key) {
        $key = $this->prefix . $key;

        $res = $this->redisObj->zCard($key);
        return $res;
    }

    /**
     * 添加到有序集
     * @param string $key 键名
     * @param double $score 权重值
     * @param mixed  $val 键值
     * @return int
     */
    public function zAdd($key ,$score, $val) {
        $key = $this->prefix . $key;
        $value = json_encode($val);

        return $this->redisObj->zAdd($key ,$score ,$value);
    }

    /**
     * 从有序集获取指定范围数据,根据score从低到高,有相同的score的元素而言，将会按照递减的字典顺序进行排列
     * @param string $key 键名
     * @param long $start 开始位数（0为第一个，-1为倒数第一个）
     * @param long $end 结束位置
     * @param boolean $withScores 是否返回权重值，默认为不返回
     * @return array
     */
    public function zRange($key,$start,$end,$withScores = false) {
        $key = $this->prefix . $key;

        $res = $this->redisObj->zRange($key,$start,$end,$withScores);
        return $res;
    }

    /**
     * 从有序集获取指定范围数据,根据score从高到低,有相同的score的元素而言，将会按照递减的字典顺序进行排列
     * @param string $key 键名
     * @param long $start 开始位数（0为第一个，-1为倒数第一个）
     * @param long $end 结束位置
     * @param boolean $withScores 是否返回权重值，默认为不返回
     * @return array
     */
    public function zRevRange($key,$start,$end,$withScores = false) {
        $key = $this->prefix . $key;

        $res = $this->redisObj->zRevRange($key,$start,$end,$withScores);
        return $res;
    }

    /**
     * 从有序集删除范围内成员
     * @param string $key 键名
     * @param long $start
     * @param long $end
     * @return int
     */
    public function zRemRangeByRank($key,$start,$end) {
        $key = $this->prefix . $key;

        $ret = $this->redisObj->zRemRangeByRank($key,$start,$end);
        return $ret;
    }

    /**
     * 从有序集删除指定成员
     * @param string $key 键名
     * @param array $member 成员
     * @return boolean
     */
    public function zDelete($key,$member) {
        $key = $this->prefix . $key;
        $member = json_encode($member);

        $ret = $this->redisObj->zDelete($key,$member);
        return $ret;
    }

    /**
     * 从有序集查找某个成员
     * @param string $key 键名
     * @param array $member 成员
     * @return mix 存在返回score值，不存返回false
     */
    public function zScore($key,$member) {
        $key = $this->prefix . $key;
        $member = json_encode($member);

        $ret = $this->redisObj->zScore($key,$member);
        return $ret;
    }

    /**
     * 订阅主题
     * @param array $topicArr 订阅主题数组
     * @param string $callback 回调函数
     */
    public function subscribe($topicArr , $callback) {
        foreach($topicArr as &$topic){
            $topic = $this->prefix . $topic;
        }

        $this->redisObj->subscribe($topicArr , $callback);
    }

    /**
     * 发布主题
     * @param string $key 键名
     * @param mixed  $val 键值
     * @return int
     */
    public function publish($key , $val) {
        $key = $this->prefix . $key;
        $value = json_encode($val);

        return $this->redisObj->publish($key , $value);
    }
    /**
     * 获取队列长度
     * @param string $key 键名
     * @return int
     */
    public function lLen($key) {
        $key = $this->prefix . $key;

        return $this->redisObj->lLen($key);
    }

    /**
     * 向哈希中添加元素
     * @param $key - 哈希表
     * @param $field - 域
     * @param $value - 值
     * @return mixed
     */
    public function hSet($key,$field,$value){
        $key = $this->prefix . $key;
        return $this->redisObj->hSet($key,$field,$value);
    }


    /**
     * 获取哈希表中域的值
     * @param $key - 哈希表
     * @param $field - 域
     * @return mixed
     */
    public function hGet($key,$field){
        $key = $this->prefix . $key;
        return $this->redisObj->hGet($key,$field);
    }

    /**
     * 获取哈希表中元素个数
     * @param $key - 哈希表
     * @return mixed
     */
    public function hLen($key){
        $key = $this->prefix . $key;
        return $this->redisObj->hLen($key);
    }

    /**
     * 删除哈希表中的域
     * @param $key - 哈希表
     * @param $field - 域
     * @return mixed
     */
    public function hDel($key,$field){
        $key = $this->prefix . $key;
        return $this->redisObj->hDel($key,$field);
    }

    /**
     * 获取哈希表中中所有的域及其对应的值
     * @param $key - 哈希表
     * @return mixed
     */
    public function hGetAll($key){
        $key = $this->prefix . $key;
        return $this->redisObj->hGetAll($key);
    }

    /**
     * 设置过期时间
     * @param $key
     * @param $timeout
     * @return mixed
     */
    public function expire($key,$timeout){
        $key = $this->prefix . $key;
        return $this->redisObj->expire($key,$timeout);
    }

    /** 计数加1返回
     * @param $key
     * @return mixed
     */
    public function incr($key){
        $key = $this->prefix . $key;
        return $this->redisObj->incr($key);
    }

    /** 计数减一返回
     * @param $key
     * @return mixed
     */
    public function decr($key){
        $key = $this->prefix . $key;
        return $this->redisObj->decr($key);
    }

    /** 删除数据
     * @param $key
     * @return mixed
     */
    public function del($key){
        $key = $this->prefix . $key;
        return $this->redisObj->del($key);
    }

    /**
     * 集合添加元素
     * @param $key
     * @param $value
     */
    public function sAdd($key, $value)
    {
        $pk = $this->prefix . $key;
        return $this->redisObj->sAdd($pk, $value);
    }

    /**
     * 获取集合内容
     * @param $key
     */
    public function sMembers($key)
    {
        $pk = $this->prefix . $key;
        return $this->redisObj->sMembers($pk);
    }

    /**
     * 移除集合内元素
     * @param $key
     * @param $value
     */
    public function sRem($key, $value)
    {
        $pk = $this->prefix . $key;
        return $this->redisObj->sRem($pk, $value);
    }

    /**
     * 集合中元素的数量
     * @param $key
     * @return int
     */
    public function sCard($key)
    {
        $pk = $this->prefix . $key;
        return $this->redisObj->sCard($pk);
    }

    /**
     * 集合的差
     * @param $key1
     * @param $key2
     * @return array
     */
    public function sDiff($key1, $key2)
    {
        $pk1 = $this->prefix . $key1;
        $pk2 = $this->prefix . $key2;
        return $this->redisObj->sDiff($pk1, $pk2);
    }

    /**
     * 向hash表中添加多对域值
     * @param $key
     * @param $values
     */
    public function hMSet($key, $values)
    {
        $pk = $this->prefix . $key;
        return $this->redisObj->hMSet($pk, $values);
    }

    /**
     * 获取hash表所有的域
     * @param $key - 哈希表
     * @return mixed
     */
    public function hKeys($key){
        $key = $this->prefix . $key;
        return $this->redisObj->hKeys($key);
    }

    /**
     * 有序集合增加score
     * @param $key
     * @param $value
     * @param $score
     * @return mixed
     */
    public function zIncrBy($key, $value, $score){
        $key = $this->prefix . $key;
        $value = json_encode($value);
        return $this->redisObj->zIncrBy($key, $score, $value);
    }

    /**
     * 有序集合返回一定范围分值的元素
     * @param $key
     * @param $min
     * @param $max
     * @param $withscore
     * @return mixed
     */
    public function zRangeByScore($key, $max, $min = 0, $withscore = false){
        $key = $this->prefix . $key;
        return $this->redisObj->zRangeByScore($key, $min, $max, ['withscores'=>$withscore]);
    }

    /**
     * 从有序集删除范围内成员
     * @param string $key 键名
     * @param long $start
     * @param long $end
     * @return int
     */
    public function zRemRangeByScore($key,$start,$end) {
        $key = $this->prefix . $key;
        $ret = $this->redisObj->zRemRangeByScore($key,$start,$end);
        return $ret;
    }

    /**
     * 有序集合返回一定范围分值的元素
     * @param $key
     * @param $min
     * @param $max
     * @param $withscore
     * @return mixed
     */
    public function zRangeLimitByScore($key, $max, $min = 0, $withscore = false, $limit = []){
        $key = $this->prefix . $key;
        $other['withscores'] = $withscore;
        if(is_array($limit) && sizeof($limit) > 0){
            $other['limit'] = $limit;
        }
        return $this->redisObj->zRangeByScore($key, $min, $max, $other);
    }

    /**
     * watch 监视 key
     * @param $key
     * @return mixed
     */
    public function watch($key) {
        $key = $this->prefix . $key;
        return $this->redisObj->watch($key);
    }
}
