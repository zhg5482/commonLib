<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/4/1
 * Time: 下午5:17
 */

namespace App\Lib\FFmPeg;

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
     * 音频转码
     * @param $filePath 文件url路径
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
                $format = new FFMpeg\Format\Audio\Mp3();
                break;
            case "flac":
                $format = new FFMpeg\Format\Audio\Flac();
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
        //todo::文件保存路径 fastdfs
        return $audio->save($format,$filename.'.'.$fileType);
    }

    /**
     * 根据视频生成gif图片
     * @param $videoPath 视频文件
     * @return string
     */
    public function makeGif($videoPath){
        $video = \FFMpeg\FFMpeg::create()->open($videoPath);
        $new_file = time().uniqid().'.gif';
        $video
            ->gif(FFMpeg\Coordinate\TimeCode::fromSeconds(2), new FFMpeg\Coordinate\Dimension(640, 480), 3)
            ->save($new_file);
        return $new_file;
    }

    /**
     * 根据视频时间提取图片 42秒
     * @param $videoPath 视频文件
     * @return \FFMpeg\Media\Frame
     */
    public function getVideoPicture($videoPath){
        $video = \FFMpeg\FFMpeg::create()->open($videoPath);
        $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(42));
        return $frame->save('image.jpg');
    }

    /**
     * @param $videoPath 视频文件
     * @param $watermarkPath 水印文件
     */
    public function waterVideo($videoPath,$watermarkPath){
        $video = \FFMpeg\FFMpeg::create()->open($videoPath);
        $video
            ->filters()
            ->watermark($watermarkPath, array(
                'position' => 'relative',//absolute 绝对路径
                'bottom' => 50,
                'right' => 50,
            ));
    }
}