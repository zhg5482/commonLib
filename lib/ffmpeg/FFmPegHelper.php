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

    /**
     * 图片加水印
     * @param $imagePath  图片文件路径
     * @param $waterContent  水印文字
     */
    public function waterImage($imagePath,$waterContent) {
        //2.获取图片信息
        $info = getimagesize($imagePath);
        //3.通过编号获取图像类型
        $type = image_type_to_extension($info[2],false);
        //4.在内存中创建和图像类型一样的图像
        $fun = "imagecreatefrom".$type;
        //5.图片复制到内存
        $image = $fun($imagePath);

        /*操作图片*/
        //1.设置字体的路径
        $font = "simkai.ttf";
        //3.设置字体颜色和透明度
        $color = imagecolorallocatealpha($image, 255, 255, 255, 0);
        //4.写入文字 (图片资源，字体大小，旋转角度，坐标x，坐标y，颜色，字体文件，内容)
        imagettftext($image, 30, 0, 100, 60, $color, $font, $waterContent);
        /*输出图片*/
        //浏览器输出
        header("Content-type:".$info['mime']);
        $fun = "image".$type;
        $fun($image);
        //保存图片
        $fun($image,'bg_res.'.$type);
        /*销毁图片*/
        imagedestroy($image);
    }

    /**
     *
     * 制作缩略图
     * @param $src_path string 原图路径
     * @param $max_w int 画布的宽度
     * @param $max_h int 画布的高度
     * @param $flag bool 是否是等比缩略图  默认为false
     * @param $prefix string 缩略图的前缀  默认为'sm_'
     *
     */
    public function thumb($src_path,$max_w,$max_h,$prefix = 'sm_',$flag = true){

        //获取文件的后缀
        $ext=  strtolower(strrchr($src_path,'.'));

        //判断文件格式
        switch($ext){
            case '.jpg':
                $type='jpeg';
                break;
            case '.gif':
                $type='gif';
                break;
            case '.png':
                $type='png';
                break;
            default:
                $this->error='文件格式不正确';
                return false;
        }


        //拼接打开图片的函数
        $open_fn = 'imagecreatefrom'.$type;
        //打开源图
        $src = $open_fn($src_path);
        //创建目标图
        $dst = imagecreatetruecolor($max_w,$max_h);

        //源图的宽
        $src_w = imagesx($src);
        //源图的高
        $src_h = imagesy($src);

        //是否等比缩放
        if ($flag) { //等比

            //求目标图片的宽高
            if ($max_w/$max_h < $src_w/$src_h) {

                //横屏图片以宽为标准
                $dst_w = $max_w;
                $dst_h = $max_w * $src_h/$src_w;
            }else{

                //竖屏图片以高为标准
                $dst_h = $max_h;
                $dst_w = $max_h * $src_w/$src_h;
            }
            //在目标图上显示的位置
            $dst_x=(int)(($max_w-$dst_w)/2);
            $dst_y=(int)(($max_h-$dst_h)/2);
        }else{    //不等比

            $dst_x=0;
            $dst_y=0;
            $dst_w=$max_w;
            $dst_h=$max_h;
        }

        //生成缩略图
        imagecopyresampled($dst,$src,$dst_x,$dst_y,0,0,$dst_w,$dst_h,$src_w,$src_h);

        //文件名
        $filename = basename($src_path);
        //文件夹名
        $foldername=substr(dirname($src_path),0);
        //缩略图存放路径
        $thumb_path = $foldername.'/'.$prefix.$filename;

        //把缩略图上传到指定的文件夹
        imagepng($dst,$thumb_path);
        //销毁图片资源
        imagedestroy($dst);
        imagedestroy($src);

        //返回新的缩略图的文件名
        return $prefix.$filename;
    }
}