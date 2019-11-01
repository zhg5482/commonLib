<?php
namespace App\Lib\FFmPeg2;
define('FFMPEG_GET_CMD', ' -i "%s" 2>&1');

class FFmPegHelper {
    /**
     * @var
     */
    private static $instance;

    /**
     * @var
     */
    private static $ffmpeg;

    /**
     * @var
     */
    private static $ffprobe;

    /**
     *
     * @var string 视频编码
     */
    private $video_code = 'libfdk_aac';

    /**
     * @var ffmpeg 可执行目录前缀
     */
    private static $ffmpeg_cmd_prefix;

    /**
     * @var array 视频 标清\高清\超清 对照
     */
    private $video_type = array(
        '480' => array('resolving'=>array('width'=>720,'height'=>480),'bitRate'=>1800),
        '720' => array('resolving'=>array('width'=>1280,'height'=>720),'bitRate'=>3500),
        '1080' => array('resolving'=>array('width'=>1920,'height'=>1080),'bitRate'=>8500),
    );

    /**
     * FFmPegHelper constructor.
     */
    private function __construct(){}

    /**
     * @return FFmPegHelper
     */
    public static function getInstance() {
        if (null == self::$instance) {
            self::$instance =new self();
        }
        self::$ffmpeg = \FFMpeg\FFMpeg::create(\Yii::$app->params['ffmpeg_config']);
        self::$ffprobe = \FFMpeg\FFProbe::create(\Yii::$app->params['ffmpeg_config']);
        self::$ffmpeg_cmd_prefix = \Yii::$app->params['ffmpeg_config']['ffmpeg.binaries'];
        return self::$instance;
    }

    /**
     * 获取当前视频编码 [X264 编码]
     * @return \FFMpeg\Format\Video\X264
     */
    private function getVideoCode() {
        return new \FFMpeg\Format\Video\X264($this->video_code);
    }

    /**
     * 获取时间点视频时间格式
     * @param $point
     * @return \FFMpeg\Coordinate\TimeCode
     */
    private function getVideoTimeCode($point) {
        return \FFMpeg\Coordinate\TimeCode::fromSeconds($point);
    }

    /**
     * 获取视频时长
     * @param $videoPath 视频文件
     * @return mixed
     */
    public function getVideoDuration($videoPath) {
        return self::$ffprobe
            ->format($videoPath)
            ->get('duration');
    }

    /**
     * 获取视频编码
     * @param $videoPath 视频文件
     * @return mixed
     */
    public function getVideoCodeName($videoPath) {
        return self::$ffprobe
            ->streams($videoPath) // extracts streams informations
            ->videos()                      // filters video streams
            ->first()                       // returns the first video stream
            ->get('codec_name');
    }

    /**
     * 提取位置上的视频图像
     * @param $videoPath 源视频
     * @param $videoImagePath 提取的图像
     * @param int $point 提取位置 秒
     */
    public function videoImage($videoPath,$videoImagePath,$point = 2) {
        $video = self::$ffmpeg->open($videoPath);
        $frame = $video->frame($this->getVideoTimeCode($point));//提取第几秒的图像
        $frame->save($videoImagePath);
    }

    /**
     * 提取视频 gif 图
     * @param $videoPath 源视频
     * @param $gitImagePath gif 图地址
     * @param int $start 起始位置
     * @param int $width 图片宽度
     * @param int $height 图片高度
     * @param int $duration 提取时长
     */
    public function videoGifImage($videoPath,$gitImagePath,$start = 10,$width = 400,$height = 200, $duration = 3) {
        $video = self::$ffmpeg->open($videoPath);
        $video->gif($this->getVideoTimeCode($start), new \FFMpeg\Coordinate\Dimension($width, $height), $duration)->save($gitImagePath);
    }

    /**
     * 合并视频
     * @param $videoPath 源分片视频中的一个
     * @param $concatPath 合并后的视频
     * @param array $slicePath 分片视频列表 array($v1,$v2,$v3)
     */
    public function videoConcat($videoPath,$concatPath,$slicePath = array()) {
        $video = self::$ffmpeg->open($videoPath);
        $video->concat($slicePath)->saveFromSameCodecs($concatPath, TRUE);
    }

    /**
     * 视频加水印图片
     * @param $videoPath 源视频
     * @param int $bottom 水印距视频底部距离
     * @param int $right 水印距视频右侧距离
     * @param $image 水印图片
     * @param $waterMarkPath 生成后的水印图片
     * @param $position
     */
    public function videoWaterMark($videoPath,$image,$waterMarkPath,$position = 'relative',$bottom = 50,$right = 50) {
        $video = self::$ffmpeg->open($videoPath);
        $video->filters()->watermark($image, array(
            'position' => $position,
            'bottom' => $bottom,
            'right' => $right
        ));
        $video->save($this->getVideoCode(), $waterMarkPath);
    }

    /**
     * 获取转码码率
     * @param $videoPath 视频文件
     * @return int 码率
     */
    private function getFileBitRate($videoPath) {
        $video_info = $this->videoInfo($videoPath);
        $origin_bit_rate = intval(($video_info[0]['size']*8/$video_info[0]['seconds'])/1000);

        $video_height = $video_info[0]['height']; //获取视频类别
        if (!isset($this->video_type[$video_height])) {
            return $origin_bit_rate;
        }
        $chose_bit_rate = $this->video_type[$video_height]['bitRate']; //可选值码率
        if ($origin_bit_rate < $chose_bit_rate ) {
            return $origin_bit_rate;
        }
        return $chose_bit_rate;
    }

    /**
     * 视频转换格式
     * @param $videoPath 源视频
     * @param $transPath 转换后视频
     * @param int $kiloBitrate 设置视频比特率
     * @param int $channel 声道设置，1单声道，2双声道，3立体声
     * @param int $audioKiloBitrate 设置音频比特率
     * @return bool
     */
    public function videoTransType($videoPath,$transPath,$channel = 2,$audioKiloBitrate = 256,$kiloBitrate = 1000) {
        try{
            $video = self::$ffmpeg->open($videoPath);
            $format = $this->getVideoCode();
            $kiloBitrate = $this->getFileBitRate($videoPath);

            $format->setKiloBitrate($kiloBitrate)
                ->setAudioChannels($channel)
                ->setAudioKiloBitrate($audioKiloBitrate);

            $res = $video->save($format, $transPath);
        }catch (\Exception $e) {
            \Yii::info('视频转换格式失败: '.var_export($e->getMessage(),true));
            return false;
        }
        return $res;
    }

    /**
     * 视频分割
     * @param $videoPath 源视频
     * @param $clipPath 分割后的视频
     * @param $start 起始位置
     * @param $duration 分割时长
     * @return bool
     */
    public function videoClip($videoPath,$clipPath,$start,$duration) {
        try{
            $video = self::$ffmpeg->open($videoPath);
            $clip = $video->clip($this->getVideoTimeCode($start), $this->getVideoTimeCode($duration));
            $res = $clip->save($this->getVideoCode(), $clipPath);
        }catch (\Exception $e) {
            \Yii::info('视频分割失败: '.var_export($e->getMessage(),true));
            return false;
        }
        return $res;
    }

    /**
     * 调整视频分辨率
     * @param $videoPath
     * @param $resizePath
     * @param int $width
     * @param int $height
     * @return bool
     */
    public function videoResize($videoPath,$resizePath,$width=320,$height=240) {
        try{
            $video = self::$ffmpeg->open($videoPath);
            $video->filters()
                ->resize(new \FFMpeg\Coordinate\Dimension($width, $height))
                ->synchronize();
            $res = $video->save($this->getVideoCode(), $resizePath);
        }catch (\Exception $e) {
            \Yii::info('视频分辨率调整失败: '.var_export($e->getMessage(),true));
            return false;
        }
        return $res;
    }

    /**
     * 获取视频信息
     * @param $videoPath 源视频
     * @return mixed
     */
    public function getVideoInfo($videoPath) {
        return self::$ffprobe->format($videoPath);
    }

    /**
     * 获取视频文件信息
     * @param $videoPath 视频文件
     * @return array
     */
    public function videoInfo($videoPath) {
        ob_start();
        passthru(sprintf(self::$ffmpeg_cmd_prefix.FFMPEG_GET_CMD, $videoPath));
        $video_info = ob_get_contents();
        ob_end_clean();
        $returnData = array();
        $array_ = explode("\n", $video_info);
        foreach ($array_ as $oneLine) {
            if(strstr($oneLine,'Duration:')){
                preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $oneLine, $match);
                $returnData['duration'] = $match[1]; //播放时间
                $arr_duration = explode(':', $match[1]);
                $returnData['seconds'] = $arr_duration[0] * 3600 + $arr_duration[1] * 60 + $arr_duration[2]; //转换播放时间为秒数
                $returnData['start'] = $match[2]; //开始时间
                $returnData['bitrate'] = $match[3]; //码率(kb)
            }
            if(strstr($oneLine,'Video:')){
                //去掉括号，因为里面可能会包含逗号照成后面正则匹配错误
                $oneLine = preg_replace('/\(([^\)]+)\)/','',$oneLine);
                preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $oneLine, $match);
                $returnData['vcodec'] = $match[1]; //视频编码格式
                $returnData['vformat'] = $match[2]; //视频格式
                $returnData['resolution'] = $match[3]; //视频分辨率
                $arr_resolution = explode('x', $match[3]);
                $returnData['width'] = $arr_resolution[0];
                $returnData['height'] = $arr_resolution[1];
            }
            if(strstr($oneLine,'Audio:')){
                //去掉括号，因为里面可能会包含逗号照成后面正则匹配错误
                $oneLine = preg_replace('/\(([^\)]+)\)/','',$oneLine);
                //preg_match("/Audio: (\w*), (\d*) Hz/", $oneLine, $match);
                preg_match("/Audio: (.*), (\d*) Hz/", $oneLine, $match);
                $returnData['acodec'] = $match[1]; //音频编码
                $returnData['asamplerate'] = $match[2]; //音频采样频率
            }
        }
        if(isset($returnData['seconds']) && isset($returnData['start'])){
            $returnData['play_time'] = $returnData['seconds'] + $returnData['start']; // 实际播放时间
        }
        $returnData['size'] = filesize($videoPath); //文件大小
        $video_info = iconv('gbk','utf8', $video_info);
        return array($returnData,$video_info);
    }
}
