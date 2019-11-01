<?php
namespace App\Lib\FFmPeg;
define('FFMPEG_GET_CMD', '/usr/local/bin/ffmpeg -i "%s" 2>&1');

class FFmPegHelper1 {


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
        self::$ffmpeg = \FFMpeg\FFMpeg::create(array(
            'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/local/bin/ffprobe',
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
        ));
        self::$ffprobe = \FFMpeg\FFProbe::create(array(
            'ffprobe.binaries' => '/usr/local/bin/ffprobe',
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
        ));
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
     * 视频转换格式
     * @param $videoPath 源视频
     * @param $transPath 转换后视频
     * @param int $kiloBitrate 设置视频比特率
     * @param int $channel 声道设置，1单声道，2双声道，3立体声
     * @param int $audioKiloBitrate 设置音频比特率
     * @return bool
     */
    public function videoTransType($videoPath,$transPath,$kiloBitrate = 1000,$channel = 2,$audioKiloBitrate = 256) {
        try{
            $video = self::$ffmpeg->open($videoPath);
            $format = $this->getVideoCode();

            $format
                ->setKiloBitrate($kiloBitrate)
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
     * 获取视频文件信息
     * @param $videoPath 视频文件
     * @return array
     */
    public function videoInfo($videoPath) {
        ob_start();
        passthru(sprintf(FFMPEG_GET_CMD, $videoPath));
        $video_info = ob_get_contents();
        ob_end_clean();

        // 使用输出缓冲，获取ffmpeg所有输出内容
        $ret = array();

        // Duration: 00:33:42.64, start: 0.000000, bitrate: 152 kb/s
        if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $video_info, $matches)){
            $ret['duration'] = $matches[1]; // 视频长度
            $duration = explode(':', $matches[1]);
            $ret['seconds'] = $duration[0]*3600 + $duration[1]*60 + $duration[2]; // 转为秒数
            $ret['start'] = $matches[2]; // 开始时间
            $ret['bitrate'] = $matches[3]; // bitrate 码率 单位kb
        }

        // Stream #0:1: Video: rv20 (RV20 / 0x30325652), yuv420p, 352x288, 117 kb/s, 15 fps, 15 tbr, 1k tbn, 1k tbc
        if(preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $video_info, $matches)){
            $ret['vcodec'] = $matches[1];  // 编码格式
            $ret['vformat'] = $matches[2]; // 视频格式
            $ret['resolution'] = $matches[3]; // 分辨率
            list($width, $height) = explode('x', $matches[3]);
            $ret['width'] = $width;
            $ret['height'] = $height;
        }

        // Stream #0:0: Audio: cook (cook / 0x6B6F6F63), 22050 Hz, stereo, fltp, 32 kb/s
        if(preg_match("/Audio: (.*), (\d*) Hz/", $video_info, $matches)){
            $ret['acodec'] = $matches[1];  // 音频编码
            $ret['asamplerate'] = $matches[2]; // 音频采样频率
        }

        if(isset($ret['seconds']) && isset($ret['start'])){
            $ret['play_time'] = $ret['seconds'] + $ret['start']; // 实际播放时间
        }

        $ret['size'] = filesize($videoPath); // 视频文件大小
        $video_info = iconv('gbk','utf8', $video_info);
        return array($ret, $video_info);
    }
}
