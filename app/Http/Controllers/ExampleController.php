<?php

namespace App\Http\Controllers;
use App\Lib\FFmPeg\FFmPegHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Lib\ElasticSearch\Es;
use App\Lib\FastDfs\FastDfsHelper;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //
    public function index() {


    }

    public function throttleTest() {
        echo "throttleTest";
    }

    /**
     * 生成serviceID
     * @return string
     */
    public function makeServiceId() {

    }

    /**
     * ffmpeg 测试
     */
    public function ffmPegTest() {
         FFmPegHelper::getInstance()->audioTransform();
    }
    /**
     * fastdfs上传
     */
    public function fastDfsTest() {
        $file = $_FILES['file'];
        $ret = FastDfsHelper::getInstance()->uploadFile($file);
        if (!$ret) {
            echoToJson('error',array());
        }
        return config('app')['fileUrl'].'/'.$ret['group_name'].'/'.$ret['filename'];
    }

    /**
     * redis test
     * @return mixed
     */
    public function redisTest() {
        Redis::setex('site_name', 10, 'Lumen的redis');
        return Redis::type('site_name');
    }

    /**
     * db test
     */
    public function dbTest() {
        $res = DB::table('category')->get();
        echoToJson('Request success',$res);
    }

    /**
     * es test
     * @throws \Exception
     */
    public function esTest() {

        $es = new Es();
        $data = [
            // 'id'=>'1',
        'title'=>'标题',
        'content'=>'内容'];## 字段content，字段值　

        $res = $es->searchMulti($data);
        echoToJson('Request success',$res);
    }
}
