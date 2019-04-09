<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/4/9
 * Time: 下午8:48
 */

namespace App\Lib\WeChat;
use EasyWeChat\Factory;

class WeChatHelper {

    /**
     * @var
     */
    private static $_instance;

    /**
     * WeChatHelper constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public static function getInstance(){
        if(null == self::$_instance) {
            $config = config('weChat');
            self::$_instance = Factory::officialAccount($config);
        }
        return self::$_instance;
    }
}