<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/7/8
 * Time: 下午3:50
 */
namespace App\Lib\rtmp;

class RTmpHelp{

    private $request;
    /**
     * @param $request
     */
    public function run($request) {
        $this->request = $request;
        call_user_func(array($this, $this->request['call']));
    }

    /**
     * 授权地址回调
     * 推流时会请求
     * 这个指令设置了发布命令回调，如果这个地址返回HTTP 2XX代码继续RTMP会议，如果返回HTTP重定向3XX ，则会重定向到指定rtmp地址上（当然需要配置，这里就不做详细配置了，可以去官方文档中参考配置），如果返回其他RTMP连接断开
     */
    private function publish() {
        \Illuminate\Support\Facades\Log::info("on_publish");
        if ($this->request['name'] == 'myStream'){
            header('HTTP/1.0 404 Not Found');
        }
        header('HTTP/1.0 200');
    }

    /**
     * 推流结束时会请求
     */
    private function publish_done() {
        \Illuminate\Support\Facades\Log::info("on_publish_done");
    }
}