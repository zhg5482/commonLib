<?php

namespace App\Http\Middleware;

use Closure;

class signVerification
{
    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    public $apiServiceConfig;

    /**
     * signVerification constructor.
     */
    public function __construct()
    {
        $this->apiServiceConfig = config('apiService');
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
        $route = $request->path();

        if (!$this->requestLimit($route,$request))
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
    public function requestLimit($request_url,$request)
    {
        //serviceId 验证
        $params = $request->input();
        if (empty($params))
        {
            return false;
        }

        if (!isset($params['serviceId']) || !in_array($params['serviceId'],array_keys($this->apiServiceConfig['serviceId_mapping'])) ||
            !in_array($request_url,$this->apiServiceConfig['serviceId_mapping'][$params['serviceId']]))
        {
            return false;
        }
        //忽略sign验证
        if (in_array($request_url,$this->apiServiceConfig['ignore_api'])) {
            return true;
        }
        //sign验证
        if (!isset($params['sign'])) {
            return false;
        }
        if (!verificationSign($params,$this->apiServiceConfig['api_token'],$this->apiServiceConfig['ignore_verification_fields']))
        {
            return false;
        }
        return true;
    }
}
