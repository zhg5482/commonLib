<?php

namespace Lib\Cache;

/**
 * memcached缓存类
 * 当采用memcached缓存集群时，将开启分布式一致性hash算法
 * Class Memcached
 * @package Lib\Cache
 */
class Memcached extends BaseCache
{

    /**
     * memcached服务器配置信息
     * @var array 每个数组元素包括ip、端口和权重
     */
    private $servers = array();

    /**
     * 是否开启分布式部署
     *
     * @var boolean
     */
    private $distribution = true;

    /**
     * memcached实例
     * @var Mecached
     */
    private $mem = null;

    /**
     *
     * @var string
     */
    private $prefix = "";

    /**
     * 初始化memached，将memached集群加到连接池，并启用一致性hash
     * @param array  $servers      类似:$servers = array(array('host'=>'127.0.0.1','port'=>11211,'weight'=>1), array('host'=>'127.0.0.1','port'=>11212,'weight'=>1))
     * @param string $prefix       对于各个公用缓存的数据，以其平台标示作为命名空间，隔离不同平台的数据，
     * @param boolean $distributio 缓存是否采用分布式部署
     * @throws Exception
     */
    public function __construct(array $servers, $namespace = "", $distribution = true)
    {
        if (!is_array($servers) || empty($servers)) {
            throw new \Exception("缓存服务器连接信息不能为空！");
            exit;
        } else {
            foreach ($servers as $row) {
                if (!is_array($row) || !isset($row['host']) || !isset($row['port'])) {// || !isset($row['weight'])
                    throw new \Exception("Memcached缓存服务器连接信息不能为空！");
                    exit;
                }
            }
        }
        $this->servers = $servers;
        if(is_string($namespace)){
            $this->prefix  = $namespace;
        }
        $this->distribution = $distribution;
        $this->mem = new \Memcached;

        $this->mem->setOption(\Memcached::OPT_SERVER_FAILURE_LIMIT, 2); //重连次数
        $this->mem->setOption(\Memcached::OPT_TCP_NODELAY, true); //关闭延迟
        //采用分布式部署
        if ($this->distribution) {
            //开启一致性哈希         取模（默认）/一致性
            $this->mem->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
            //移除失效服务器
            $this->mem->setOption(\Memcached::OPT_REMOVE_FAILED_SERVERS, true);
            //开启ketama算法兼容，注意，打开本算法时，sub_hash会使用KETAMA默认的MD5
            $this->mem->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        }
        //将服务器增加到连接池
        $this->mem->addServers($this->servers);
    }

    /**
     * 设置缓存条目
     * @param string $key    缓存的键
     * @param mixed  $var    缓存的值
     * @param int    $expire 缓存国企时间，单位是秒
     * @return boolean       设置缓存是否成功
     */
    public function set($key = '', $var = null, $expire = 0)
    {
        $key = $this->prefix . $key;
        return $this->mem->set($key, $var, $expire);
    }

    /**
     * 获取单条缓存条目
     * @param string $key 缓存数据的键
     * @return string or false 缓存
     */
    public function get($key = "")
    {
        $key = $this->prefix . $key;
        return $this->mem->get($key);
    }

    /**
     * 删除缓存
     * @param string $key 缓存数据的键
     * @param int $secend
     * @return boolean
     */
    public function delete($key, $secend = 0)
    {
        $key = $this->prefix . $key;
        return $this->mem->delete($key, $secend);
    }

    /**
     * 将多个键值对映射到一台服务器上，提高缓存的响应速度
     *
     * 采用分布式部署，建议保存关联数据时建议使用该方法代替set方法
     * @param string  $server_key 本键名用于识别储存和读取值的服务器。
     * @param array   $items      存放在服务器上的键／值对数组。
     * @param int     $expire     到期时间，默认为 0
     * @return boolean
     */
    public function setMultiByKey($server_key, array $items, $expire = 0)
    {
        return $this->mem->setMultiByKey($server_key, $items, $expire);
    }

    /**
     * 从特定服务器检索多个元素
     * @param string $server_key 本键名用于识别储存和读取值的服务器。
     * @param array $keys        要检索的key的数组
     * @return array             返回检索到的元素的数组 或者在失败时返回 FALSE
     */
    public function getMultiByKey($server_key, array $keys)
    {
        return $this->mem->getMultiByKey($server_key, $keys);
    }

    /**
     * 返回option指定的Memcached选项的值。一些选项是和libmemcached中相对应的， 也有一些特殊的选项仅仅是扩展自身的。
     * 关于选项的更多信息请查看Memcached Constants。
     * @param  int     $option Memcached::OPT_*系列常量中的一个。
     * @return mixed   返回请求的选项的值，或者在发生错误时返回FALSE。
     */
    public function getOption($option = 0)
    {
        return $this->mem->getOption($option);
    }

    /**
     * 返回一个包含所有可用memcache服务器状态的数组
     * @return array 服务器统计信息数组， 每个服务器一项。
     */
    public function getStats()
    {
        return $this->mem->getStats();
    }

}
