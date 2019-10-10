<?php

namespace Lib\Cache;

/**
 * 缓存操作类
 * Class Manager
 * @package Lib\Cache
 */
class Manager
{

    /**
     * 缓存操作实例单例模式
     * @var array
     */
    private static $instances = array();

    /**
     * 获取缓存连接实例
     * @param array  $servers 缓存服务器连接信息
     * @param string $namespace 命名空间，用于区分不同平台的数据，实际应中将其作为键的前缀
     * @param string $cacheType 缓存类型，默认使用memcached缓存
     * @return object  返回缓存实例
     * @throws Exception
     */
    public static function getInstance(array $servers = [], $namespace = "", $cacheType = 'memcached')
    {
        $redisConfig = config('database.redis');
        if(empty($servers)){
            switch($cacheType){
                case 'redis':
                    $servers = isset($redisConfig['servers']) ? $redisConfig['servers'] : $redisConfig['common']['servers'];;
                    break;
                default:
                    $servers = isset($redisConfig['servers']) ? $redisConfig['servers'] : $redisConfig['common']['servers'];;
                    break;
            }
        }
        if (!is_array($servers) || empty($servers)) {
            throw new \Exception("缓存服务器连接信息不能为空！");
            exit;
        }
        //将键md5加密
        if(is_string($namespace)){
            $key = md5(serialize($servers).$namespace);
        }else{
            $key = md5(serialize($servers));
        }
        if (!isset(self::$instances[$key])) {
            switch ($cacheType) {
                case 'redis':
                    if(empty($servers)){
                        $servers = $redisConfig['common']['servers'];
                    }
                    self::$instances[$key] = new Redis($servers, $namespace);
                    break;
                default :#后续整理 todo
                    self::$instances[$key] = new Memcached($servers, $namespace);
                    break;
            }
        }
        return self::$instances[$key];
    }

}
