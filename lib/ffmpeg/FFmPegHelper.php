<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/4/1
 * Time: 下午5:17
 */

namespace App\Lib\FFmPeg;
define('FFMPEG_GET_CMD', '/usr/local/bin/ffmpeg -i "%s" 2>&1');
define('FFMPEG_CONCAT_CMD', '/usr/local/bin/ffmpeg -f concat -i "%s" -c copy "%s" 2>&1');
define('FFMPEG_TRANS_CMD', '/usr/local/bin/ffmpeg -i "%s" -vcodec "%s" "%s" 2>&1');
define('FFMPEG_CUT_CMD', '/usr/local/bin/ffmpeg -ss "%s" -t "%s" -i "%s" -vcodec copy -acodec copy "%s" 2>&1');
define('FFMPEG_WATER_CMD', '/usr/local/bin/ffmpeg -i "%s" -vf drawtext=fontcolor=white:text="%s":x="%s":y="%s":fontsize="%s":fontcolor="%s":shadowy="%s" "%s" 2>&1');

class FFmPegHelper {

    /**
     * @var
     */
    public static $instance;

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
        return self::$instance;
    }

    /**
     * 图片加水印
     * @param $filename
     * @param $text
     * @param int $x
     * @param int $y
     * @param int $fontsize
     * @param string $fontcolor
     * @param int $shadowy
     * @param $waterfilename
     */
    public function waterImage($filename,$text,$waterfilename,$x=0,$y=100,$fontsize=24,$fontcolor="yellow",$shadowy=2) {
        ob_start();
        passthru(sprintf(FFMPEG_WATER_CMD, $filename,$text,$x,$y,$fontsize,$fontcolor,$shadowy,$waterfilename),$res);
        print_r($res);
        ob_end_clean();
    }

    /**
     * 音频转码
     * @param $filePath 音频文件
     * @param string $fileType 转换目标类型
     * @return bool|\FFMpeg\Media\Audio|\FFMpeg\Media\Video
     */
    public function audioTransform($filePath,$fileType = "mp3") {
        $filePathInfo = getFilePathInfo($filePath);
        $filename = explode('.',$filePathInfo['basename']);
        $filename = $filename[0];
        $extension = $filePathInfo['extension'];
        if (strtolower($extension) == strtolower($fileType)) {
            return false;
        }
        $audio = \FFMpeg\FFMpeg::create()->open($filePath);
        switch ($fileType){
            case "mp3" :
                $format = new \FFMpeg\Format\Audio\Mp3();
                break;
            case "flac":
                $format = new \FFMpeg\Format\Audio\Flac();
                break;
            case "aac":
                $format = new \FFMpeg\Format\Audio\Aac();
                break;
            case "vorbis":
                $format = new \FFMpeg\Format\Audio\Vorbis();
                break;
            case "wav":
                $format = new \FFMpeg\Format\Audio\Wav();
                break;
            default :
                $format = null;
        }
        if (null == $format) {
            return false;
        }
        $format->on('progress', function ($audio, $format, $percentage) {
            echo "$percentage % transcoded";
        });
        $format->setAudioChannels(2)->setAudioKiloBitrate(256);

        return $audio->save($format,$filename.'.'.$fileType);
    }

    /**
     * 获取视频文件信息
     * @param $file 视频文件
     * @return array
     */
    public function getVideoFileInfo($file) {
        ob_start();
        passthru(sprintf(FFMPEG_GET_CMD, $file));
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

        $ret['size'] = $this->get_file_size($file); // 视频文件大小
        $video_info = iconv('gbk','utf8', $video_info);
        return array($ret, $video_info);
    }

    /**
     * 根据视频时段生成gif图片
     * @param $videoPath 视频地址
     * @param $start 开始时间
     * @param $end 结束时间
     * @param int $width 图片宽度
     * @param int $height 图片高度
     * @return string
     */
    public function makeGif($videoPath,$start,$end,$width=640,$height=480){
        $video = \FFMpeg\FFMpeg::create()->open($videoPath);
        $new_file = time().uniqid().'.gif';
        $video
            ->gif(\FFMpeg\Coordinate\TimeCode::fromSeconds($start), new \FFMpeg\Coordinate\Dimension($width, $height), $end)
            ->save($new_file);
        return $new_file;
    }

    /**
     * 根据视频时间提取图片
     * @param $videoPath 视频地址
     * @param $savePath 提取图片保存文件名
     * @param int $second 提取图片时间点
     * @return \FFMpeg\Media\Frame
     */
    public function getVideoPicture($videoPath,$savePath,$second=42){
        $video = \FFMpeg\FFMpeg::create()->open($videoPath);
        $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($second));
        return $frame->save($savePath);
    }

    /**
     * 视频添加水印
     * @param $videoPath 视频文件
     * @param $watermarkPath 水印图片
     * @param int $bottom 水印图片距视频底部距离
     * @param int $right 水印图片距视频右侧距离
     */
    public function waterVideo($videoPath,$watermarkPath,$bottom=50,$right=50){
        $video = \FFMpeg\FFMpeg::create()->open($videoPath);
        $video
            ->filters()
            ->watermark($watermarkPath, array(
                'position' => 'relative',//absolute 绝对路径
                'bottom' => $bottom,
                'right' => $right,
            ));
    }

    /**
     * 分割视频
     * @param $start 起始位置
     * @param $dur 时长
     * @param $fileName 视频文件
     * @param $cutFileName 分割后视频文件
     */
    public function cutVideoFile($start,$dur,$fileName,$cutFileName) {
        ob_start();
        passthru(sprintf(FFMPEG_CUT_CMD, $start,$dur,$fileName,$cutFileName),$res);
        print_r($res);
        ob_end_clean();
    }

    /**
     * 视频合并
     * @param $video_list
     *$ cat mylist.txt
        file '/path/to/file1'
        file '/path/to/file2'
        file '/path/to/file3'
     *
     * @param $out_file
     */
    public function videoConcat($video_list,$out_file) {
        ob_start();
        passthru(sprintf(FFMPEG_CONCAT_CMD, $video_list,$out_file),$res);
        print_r($res);
        ob_end_clean();
    }

    /**
     * 视频转码
     * @param $videoPath
     * @param $transPath
     * @param string $trans_type h264、mpeg4、libxvid、wmv1、wmv2
     */
    public function videoTransform($videoPath,$transPath,$trans_type='mpeg4') {
        ob_start();
        passthru(sprintf(FFMPEG_TRANS_CMD, $videoPath,$trans_type,$transPath),$res);echo 232;exit;
        print_r($res);
        ob_end_clean();
    }

    public function videoTransform1($videoPath) {
        $ffmpeg = \FFMpeg\FFMpeg::create(array(
            'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/local/bin/ffprobe',
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
        ));
        $video = $ffmpeg->open($videoPath);

        $video
            ->save(new \FFMpeg\Format\Video\X264(), 'export-x264.mp4')
            ->save(new \FFMpeg\Format\Video\WMV(), 'export-wmv.wmv')
            ->save(new \FFMpeg\Format\Video\WebM(), 'export-webm.webm');
    }
}
