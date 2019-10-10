<?php

use PhpOption\Option;
use Illuminate\Container\Container;
use Dotenv\Environment\DotenvFactory;
use Laravel\Lumen\Bus\PendingDispatch;
use Illuminate\Contracts\Bus\Dispatcher;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;

if (! function_exists('abort')) {
    /**
     * Throw an HttpException with the given data.
     *
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    function abort($code, $message = '', array $headers = [])
    {
        return app()->abort($code, $message, $headers);
    }
}

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $make
     * @param  array  $parameters
     * @return mixed|\Laravel\Lumen\Application
     */
    function app($make = null, array $parameters = [])
    {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make, $parameters);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath().($path ? '/'.$path : $path);
    }
}

if (! function_exists('decrypt')) {
    /**
     * Decrypt the given value.
     *
     * @param  string  $value
     * @return string
     */
    function decrypt($value)
    {
        return app('encrypter')->decrypt($value);
    }
}

if (! function_exists('dispatch')) {
    /**
     * Dispatch a job to its appropriate handler.
     *
     * @param  mixed  $job
     * @return mixed
     */
    function dispatch($job)
    {
        return new PendingDispatch($job);
    }
}

if (! function_exists('dispatch_now')) {
    /**
     * Dispatch a command to its appropriate handler in the current process.
     *
     * @param  mixed  $job
     * @param  mixed  $handler
     * @return mixed
     */
    function dispatch_now($job, $handler = null)
    {
        return app(Dispatcher::class)->dispatchNow($job, $handler);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('database_path')) {
    /**
     * Get the path to the database directory of the install.
     *
     * @param  string  $path
     * @return string
     */
    function database_path($path = '')
    {
        return app()->databasePath($path);
    }
}

if (! function_exists('encrypt')) {
    /**
     * Encrypt the given value.
     *
     * @param  string  $value
     * @return string
     */
    function encrypt($value)
    {
        return app('encrypter')->encrypt($value);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        static $variables;

        if ($variables === null) {
            $variables = (new DotenvFactory([new EnvConstAdapter, new PutenvAdapter, new ServerConstAdapter]))
                ->createImmutable();
        }

        return Option::fromValue($variables->get($key))
            ->map(function ($value) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return;
                }

                return $value;
            })
            ->getOrCall(function () use ($default) {
                return value($default);
            });
    }
}

if (! function_exists('event')) {
    /**
     * Dispatch an event and call the listeners.
     *
     * @param  object|string  $event
     * @param  mixed   $payload
     * @param  bool    $halt
     * @return array|null
     */
    function event($event, $payload = [], $halt = false)
    {
        return app('events')->dispatch($event, $payload, $halt);
    }
}

if (! function_exists('factory')) {
    /**
     * Create a model factory builder for a given class, name, and amount.
     *
     * @param  dynamic  class|class,name|class,amount|class,name,amount
     * @return \Illuminate\Database\Eloquent\FactoryBuilder
     */
    function factory()
    {
        app('db');

        $factory = app('Illuminate\Database\Eloquent\Factory');

        $arguments = func_get_args();

        if (isset($arguments[1]) && is_string($arguments[1])) {
            return $factory->of($arguments[0], $arguments[1])->times($arguments[2] ?? null);
        } elseif (isset($arguments[1])) {
            return $factory->of($arguments[0])->times($arguments[1]);
        } else {
            return $factory->of($arguments[0]);
        }
    }
}

if (! function_exists('info')) {
    /**
     * Write some information to the log.
     *
     * @param  string  $message
     * @param  array   $context
     * @return void
     */
    function info($message, $context = [])
    {
        return app('Psr\Log\LoggerInterface')->info($message, $context);
    }
}

if (! function_exists('redirect')) {
    /**
     * Get an instance of the redirector.
     *
     * @param  string|null  $to
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * @return \Laravel\Lumen\Http\Redirector|\Illuminate\Http\RedirectResponse
     */
    function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
        $redirector = new Laravel\Lumen\Http\Redirector(app());

        if (is_null($to)) {
            return $redirector;
        }

        return $redirector->to($to, $status, $headers, $secure);
    }
}

if (! function_exists('resource_path')) {
    /**
     * Get the path to the resources folder.
     *
     * @param  string  $path
     * @return string
     */
    function resource_path($path = '')
    {
        return app()->resourcePath($path);
    }
}

if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param  string  $content
     * @param  int     $status
     * @param  array   $headers
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        $factory = new Laravel\Lumen\Http\ResponseFactory;

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($content, $status, $headers);
    }
}

if (! function_exists('route')) {
    /**
     * Generate a URL to a named route.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool|null  $secure
     * @return string
     */
    function route($name, $parameters = [], $secure = null)
    {
        return app('url')->route($name, $parameters, $secure);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        return app()->storagePath($path);
    }
}

if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string|null  $id
     * @param  array   $replace
     * @param  string|null  $locale
     * @return \Illuminate\Contracts\Translation\Translator|string|array|null
     */
    function trans($id = null, $replace = [], $locale = null)
    {
        if (is_null($id)) {
            return app('translator');
        }

        return app('translator')->trans($id, $replace, $locale);
    }
}

if (! function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return string|array|null
     */
    function __($key, $replace = [], $locale = null)
    {
        return app('translator')->getFromJson($key, $replace, $locale);
    }
}

if (! function_exists('trans_choice')) {
    /**
     * Translates the given message based on a count.
     *
     * @param  string  $id
     * @param  int|array|\Countable  $number
     * @param  array  $replace
     * @param  string|null  $locale
     * @return string
     */
    function trans_choice($id, $number, array $replace = [], $locale = null)
    {
        return app('translator')->transChoice($id, $number, $replace, $locale);
    }
}

if (! function_exists('url')) {
    /**
     * Generate a url for the application.
     *
     * @param  string  $path
     * @param  mixed  $parameters
     * @param  bool|null  $secure
     * @return string
     */
    function url($path = null, $parameters = [], $secure = null)
    {
        return app('url')->to($path, $parameters, $secure);
    }
}

if (! function_exists('validator')) {
    /**
     * Create a new Validator instance.
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return \Illuminate\Contracts\Validation\Validator
     */
    function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $factory = app('validator');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}

if (! function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Illuminate\View\View
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app('view');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}
/**
 * @param $code
 * @param $data
 */
function echoToJson($code,$data)
{
    $apiService = config('apiService');
    $code = trim($code);
    if (!isset($apiService['code'][$code]))
    {
        $code = 'Default code';
        $data = array();
    }
    $code = $apiService['code'][$code];
    $meg = $apiService['code_message'][$code];
    echo json_encode(array('code'=>$code,'message'=>$meg,'data'=>$data)); exit;
}

/**
 * @param $str
 * @return false|int
 */
function checkIsNumber($str)
{
    return preg_match('/^[\d,，\f\n\r\t\v]+$/',$str);
}

/**
 * @param $str
 * @return null|string|string[]
 */
function removeStrSpace($str)
{
    return preg_replace('# #','',$str);
}

/**
 * @param $arr
 * @return bool
 */
function check_es_config($arr)
{
    return true;
}

/**
 * @param $object
 * @return mixed
 */
function object2array($object)
{
    return json_decode(json_encode($object),true);
}

/**
 * 获取后缀
 * @param $fileName
 * @return string
 */
function getSuffix($fileName)
{/*{{{*/
 preg_match('/\.(\w+)?$/', $fileName, $matchs);
 return isset($matchs[1])?$matchs[1]:'';
}

/**
 * 接口签名
 * @param $params
 * @param $token
 * @param $ignore_fields
 * @return bool
 */
function verificationSign($params,$token,$ignore_fields)
{
    $secret = md5($token);
    $str = '';
    $sign = $params['sign'];
    unset($params['sign']);
    ksort($params);
    foreach($params as $k => $v){
        if (!in_array($k,$ignore_fields))
        {
            continue;
        }
        $str .= $k.'='.$v.'&';
    }
    $str = $str.$secret;
    return $sign == md5($str);
}

/**
 * serviceId
 * @param string $cert
 * @param string $delimiter
 * @param int $delimiter_length
 * @return string
 */
function makeServiceId($cert = '',$delimiter = '-',$delimiter_length = 8)
{
    $cert = empty($cert) ? time().uniqid() : removeStrSpace($cert);
    $str = '';
    $md5 = md5($cert);
    for ($i = 0; $i < strlen($md5);$i=$i+$delimiter_length) {
        $str .= substr($md5,$i,$delimiter_length).$delimiter;
    }
    return trim($str,$delimiter);
}

/**
 * 获取文件后缀
 * @param $fileName
 * @return mixed
 */
function getFilePathInfo($fileName)
{
    $fileInfo = parse_url($fileName);
    $result = pathinfo($fileInfo['path']);
    return $result;
}


/**
 * @param $string    要加密/解密的字符串
 * @param string $operation   类型，ENCODE 加密；DECODE 解密
 * @param string $key    密匙
 * @param int $expiry    有效期
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = 'encrypt', $expiry = 0)
{
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;
    // 密匙
    $key = md5($key ? $key : config('apiService.api_token'));
    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
        substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
    //解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
        sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
            substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)
        ) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * @param $email
 * @return bool
 */
function is_email($email) {
    return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

/**
 * @param $phone
 * @return bool
 */
function is_mobile($phone){
    if(preg_match("/^13[0-9]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0236789]{1}[0-9]{8}$/",$phone)){
        Return true;
    }else{
        Return false;
    }
}

/**
 * @param $password
 * @param int $lenth
 * @return array|string
 */
function password($password, $lenth=6) {
    if(is_numeric($lenth)){
        $pwd = array();
        $pwd['encrypt'] = random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
        $pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
        return $pwd;
    }else{
        $pwd['encrypt'] =  $lenth;
        $pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
        return $pwd['password'];
    }
}

/**
 * @param $length
 * @param string $chars
 * @return string
 */
function random($length, $chars = '0123456789') {
    $hash = '';
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 * @param $date
 * @return string
 */
function dateTimeStr($date) {
    $date = str_replace('-','',$date);
    if ($date == 19000101 || $date == 0) {
        return '';
    }
    return substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);

}

/**
 * 换行替换
 *
 * @param $string
 * @param int $repeat
 * @return null|string|string[]
 */
function replace_space($string, $repeat = 1)
{
    $string = preg_replace("/[ \s]+/u", str_repeat(" ", $repeat), $string);
    return trim($string);
}
