<?php

namespace App\Http\Middleware;

use Closure;

class signVerification
{
    /**
     * verification fields
     * @var array
     */
    private $verification_fields = array('serviceId','search_type','user_id');
    private $ignore_api = array('black_list');

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = $request->route();
        $request_url = $route[1]['as'];

        if (in_array($request_url,$this->ignore_api))
        {
            return $next($request);
        }
        $apiServiceConf = config('apiService');
        if (!$this->requestLimit($request_url,$request,$apiServiceConf))
        {
            //echoToJson('No authority',array());
        }
        return $next($request);
    }
    /**
     * request verification
     * @param $request
     * @param $serviceId_mapping
     * @return bool
     */
    public function requestLimit($request_url,$request,$apiServiceConf)
    {
        $params = $request->input();
        if (empty($params) || !isset($params['sign']))
        {
            return false;
        }
        if (!isset($params['serviceId']) || !in_array($params['serviceId'],array_keys($apiServiceConf['serviceId_mapping'])) ||
            !in_array($request_url,$apiServiceConf['serviceId_mapping'][$params['serviceId']]))
        {
            return false;
        }
        if (!$this->verificationSign($params,$apiServiceConf['api_token']))
        {
            return false;
        }
        return true;
    }
    /**
     * @param $params
     * @return bool
     */
    public function verificationSign($params,$token)
    {
        $secret = md5($token);
        $str = '';
        $sign = $params['sign'];
        unset($params['sign']);
        ksort($params);
        foreach($params as $k => $v){
            if (!in_array($k,$this->verification_fields))
            {
                continue;
            }
            $str .= $k.'='.$v.'&';
        }
        $str = $str.$secret;
        return $sign == md5($str);
    }
}
