<?php

namespace App\Http\Controllers;
use App\Lib\File\FFmpegHelper\Coordinate\Dimension;
use App\Lib\File\FFmpegHelper\FFMpeg;
use App\Lib\File\FFmpegHelper\Filters\Video\ResizeFilter;
use App\Lib\File\FFmpegHelper\Format\Video\CuvId;
use Illuminate\Http\Request;
use App\Lib\FFmPeg\FFmPegHelper;
use App\Lib\MongoDb\MongoDbHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Lib\ElasticSearch\Es;
use App\Lib\FastDfs\FastDfsHelper;

class ExampleController extends Controller
{
    /**
     * @var int
     */
    private $page_num = 20;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        //
        parent::__construct($request);
    }

    public function index() {

        //$this->coupon();

        $filename = "/usr/local/var/www/testvideo/CRSHOPG0711144643031.mp4";
        //$filename = 'http://v.libraryplus.bjadks.com/target/video/201909/1080P/20190920183109_3281.mp4';

        $ffmpeg = FFMpeg::create(array(
            'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg', // ffmpeg path
            'ffprobe.binaries' => '/usr/local/bin/ffprobe',// ffprobe path
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 24,   // The number of threads that FFMpeg should use
        ));


        $video = $ffmpeg->open($filename);
        //$format = new X264('libfdk_aac');
        $format = new CuvId('videotoolbox','h264_videotoolbox','');

        $format->on('progress', function ($video, $format, $percentage){
            echo $percentage.' ';
        });

        $video->filters()
            ->resize(new Dimension(720, 450),ResizeFilter::RESIZEMODE_INSET, true)
            ->synchronize();

        $format->setKiloBitrate(450);
        $res = $video->save($format, 'a.mp4');
    }

    private function uploadFileByTmpName() {
        $pic = $_FILES['pic'];
        move_uploaded_file($pic['tmp_name'][0],'/usr/local/var/www/a.png');
        echoToJson('Request success',json_decode($this->request->input('info'),true));
    }

    public function throttleTest() {
        echo "throttleTest";
    }

    /**
     * 生成serviceID
     * @return string
     */
    public function makeServiceId() {
        return makeServiceId('api/vi/test');
    }

    /**
     * mongodb 测试
     */
    public function mongodbTest() {
         return MongoDbHelper::getInstance()->getData('news',array('channel'=>'热点'));
    }

    /**
     * ffmpeg 测试
     */
    public function ffmPegTest() {
         $filename = 'http://v.libraryplus.bjadks.com/target/video/201909/1080P/20190920183109_3281.mp4';
         $filename = "/usr/local/var/www/commonlib/public/a.wmv";
         $res = FFmPegHelper::getInstance()->videoTransform($filename,'b.mp4');
         print_r($res);
    }

    /**
     * fastdfs上传
     */
    public function fastDfsTest() {
        $file = $_FILES['file'];
        $ret = FastDfsHelper::getInstance()->upload($file, pathinfo($file,PATHINFO_EXTENSION),false);
        var_dump($ret);
        if (!$ret) {
            echoToJson('error',array());
        }
        return config('app')['fileUrl'].'/'.$ret['group_name'].'/'.$ret['filename'];
    }

    public function uploadFile($file) {
        if (!is_array($file)) {
            $fileContent = $file;
            $fileSuffix = pathinfo($file,PATHINFO_EXTENSION);
            $is_file_buff = false;
        }else{
            $curlFile = new \CurlFile($file['tmp_name'], $file['type'], $file['name']);
            $fileSuffix = getSuffix($curlFile->getPostFilename());
            $fileContent = file_get_contents($curlFile->getFilename());
            $is_file_buff = true;
        }
        return $this->upload($fileContent,$fileSuffix,$is_file_buff);
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

    /**
     * rtmp 推送测试
     */
    public function rTmpTest() {
        $post_data = $_POST;
        if (empty($post_data))
        {
            return;
        }
        Log::info(var_export($post_data,true));
        $rtmpService = app('App\Lib\rtmp\RTmpHelp');
        $rtmpService->run($post_data);
    }

    /**
     * 秒杀测试
     */
    public function seckill() {
        header("content-type:text/html;charset=utf-8");
        $rob_total = 100;   //抢购数量
        $this->redis->watch("mywatchlist");
        $mywatchkey = $this->redis->hLen("mywatchlist");
        $this->redis->multi();
        if($mywatchkey < $rob_total){
            //设置延迟，方便测试效果。
            sleep(5);
            //插入抢购数据
            $this->redis->hSet("mywatchlist","user_id_".mt_rand(1, 9999),time());
            $rob_result = $this->redis->exec();
            if($rob_result){
                $mywatchlist = $this->redis->hGetAll("mywatchlist");
                echo "抢购成功！<br/>";
                echo "剩余数量：".($rob_total-$mywatchkey-1)."<br/>";
                echo "用户列表：<pre>";
                var_dump($mywatchlist);
            }else{
                echo "手气不好，再抢购！";exit;
            }
        }
    }

    //取随机浮点数
    function random_float($min = 0.01, $max = 10.00) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    /**
     * 红包
     */
    public function coupon() {
        $total_price = 20.00;//红包总额
        $overage = $total_price;//红包余额
        $num = 5;//设置抢红包的人数
        $reward = array($num);//每个人获得的红包（数组）
        for ($i=1;$i<=$num;$i++){
            if ($i==$num){
                $reward[$i] = $overage;
                $overage = 0;
            }else{
                $random = $this->random_float(0.01,$overage/$num*2);
                $reward[$i] = bcdiv(floor($random*100),100,2);
                $overage = bcsub($overage,$reward[$i],2);
            }
            echo "第 $i 个人抢到的红包金额：";print_r($reward[$i]);echo "剩余红包总额为： $overage";
            echo "<br />";//输出html换行标签
        }
    }

    /**
     * 分布式锁
     */
    public function luckRedis() {
        $key = 'select_algo';
        if ($res = $this->redis->get($key)) { //存在
            echo $res;
        }else{ //不存在
            $lock_key = 'select_lock';
            $lock_res = $this->redis->setnx($lock_key,time()+1);
            if (! $lock_res) {  //未获得锁
                $lock_key_time = $this->redis->get($lock_key); //获取锁过期时间
                if ($lock_key_time < time()) { // 已过期
                    $this->redis->delete($lock_key); // 删除锁
                    $lock_res = $this->redis->setnx($lock_key, time() + 1);
                }
            }
            if ($lock_res) { //获取锁成功 更新数据
                $sql = '';
                $res = 'sql command ';
                $this->redis->set($key,'123456',120);
            }else{ //获取锁失败 返回异常
                echo "重试";
            }
        }
    }

    /**
     * 分布式锁
     * @throws \Exception
     * 参考:http://www.36nu.com/post/314
     */
    public function luckRedis1() {
        $key = 'select_lock';
        $random = random_int(1,9999);
        $rs = $this->redis->set($key, $random, array('nx', 'ex' => 10));
        if ($rs) {
            //处理更新缓存逻辑
            // ......
            //先判断随机数，是同一个则删除锁
            if ($this->redis->get($key) == $random) {
                $this->redis->del($key);
            }
        }
    }
}
