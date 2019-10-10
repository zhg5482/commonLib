<?php

namespace Lib\Cache;

/**
 * 数据缓存抽象类
 * Class BaseCache
 * @package Lib\Cache
 */
abstract class BaseCache
{

    public function __construct()
    {
        ;
    }
    /**
     * 设置缓存
     */
    abstract public function set($key = '', $var = null, $expire = 0);

    /**
     * 获取缓存
     */
    abstract public function get($key = '');
}
