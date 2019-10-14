<?php

class Client {
    // RPC 服务地址映射表
    public static $rpcConfig = array(
        "Services"    => "http://127.0.0.1/Services.php",
    );

    public static function init($server){
        if (array_key_exists($server, self::$rpcConfig)) {
            $uri = self::$rpcConfig[$server];
            return new Yar_Client($uri);
        }
    }
}

$RewardScoreService = RpcClient::init("Services");
var_dump($RewardScoreService->support(1, 2));
