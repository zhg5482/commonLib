<?php

namespace App\Http\Middleware;

use Closure;
use Lib\Cache\Manager;

class VueVerification
{
    /**
     * @var
     */
    public $redis;

    /**
     * signVerification constructor.
     */
    public function __construct()
    {
        $this->redis = $this->getRedis();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->authVerification($request))
        {
            echoToJson('No authority',array());
        }
        return $next($request);
    }

    /**
     * 签名验证
     * @param $request_url
     * @param $request
     * @return bool
     */
    public function authVerification($request)
    {
        // token 验证
        $token = $request->headers->get("X-Token");
        if (!$token)
        {
            return false;
        }
        // 检查 token 是否有效
        $user_id = authcode($token, 'DECODE');
        $userInfo = $this->redis->get('passport_'.$user_id);
        if (!$userInfo) {
            return false;
        }
        //todo :: 验签
        return true;
    }

    /**
     * getRedis
     * @return mixed
     */
    private function getRedis()
    {
        if (!is_object($this->redis)) {
            $this->redis = Manager::getInstance([],'', 'redis');
        }
        return $this->redis;
    }
}
