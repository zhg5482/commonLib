<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/7/28
 * Time: 下午6:05
 */
namespace App;
use Laravel\Lumen\Application as BaseApplication;

class Application extends BaseApplication{

    public function match($method,$url,$action)
    {
        foreach ($method as $method) {
            $this->addRoute($method,$url,$action);
        }

    }
}
