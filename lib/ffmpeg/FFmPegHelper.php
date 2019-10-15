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
define('blockSize', 4*1024*1024);
header('Content-type:text/html;Charset=UTF-8');

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
     * @param $file
     * @return array
     */
    public function fileHash($file)
    {
        $f = fopen($file, "r");
        if (!$f) exit("open $file error");

        $fileSize = filesize($file);
        $buffer   = '';
        $sha      = '';
        // 一共有多少分片
        $blkcnt   = $fileSize/blockSize;
        if ($fileSize % blockSize) $blkcnt += 1;
        // 把数据装入一个二进制字符串
        $buffer .= pack("L", $blkcnt);
        if ($fileSize <= blockSize) {
            $content = fread($f, blockSize);
            if (!$content) {
                fclose($f);
                exit("read file error");
            }
            $sha .= sha1($content, TRUE);
        } else {
            for($i=0; $i<$blkcnt; $i+=1) {
                $content = fread($f, blockSize);
                if (!$content) {
                    if (feof($f)) break;
                    fclose($f);
                    exit("read file error");
                }
                $sha .= sha1($content, TRUE);
            }
            $sha = sha1($sha, TRUE);
        }
        $buffer .= $sha;
        $hash = $this->urlSafeEncode(base64_encode($buffer));
        fclose($f);
        return array($hash, null);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function urlSafeEncode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, $data);
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
     * 获取线上文件大小
     * @param $url 文件地址
     * @return bool|string
     */
    private function get_file_size($url) {
        $url = parse_url($url);

        if (empty($url['host'])) {
            return false;
        }

        $url['port'] = empty($url['post']) ? 80 : $url['post'];
        $url['path'] = empty($url['path']) ? '/' : $url['path'];

        $fp = fsockopen($url['host'], $url['port'], $error);

        if($fp) {
            $out = "GET ".$url['path']." HTTP/1.1".PHP_EOL;
            $out .= "Host: ".$url['host'].PHP_EOL;
            $out .= "Connection: Close".PHP_EOL.PHP_EOL;
            fwrite($fp, $out);

            while (!feof($fp)) {
                $str = fgets($fp);
                if (trim($str) == '') {
                    break;
                }elseif(preg_match('/Content-Length:(.*)/si', $str, $arr)) {
                    return trim($arr[1]);
                }
            }

            fclose ( $fp);
            return false;
        }else {
            return false;
        }
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
     * 视频文件添加水印
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

    /**
     * 图片加水印
     * @param $imagePath  图片文件
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
     * 制作缩略图
     * @param $src_path string 原图路径
     * @param $max_w int 画布的宽度
     * @param $max_h int 画布的高度
     * @param $flag bool 是否是等比缩略图  默认为false
     * @param $prefix string 缩略图的前缀  默认为'sm_'
     * @return string
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

    /**
     *  @param [string] $[file] [文件路径]
     *  @param [int] $[rate] [下载速度]
     *  @param boole $[forceDownload] [文件名是否中文处理]
     *  @return null
     */
    function downFile($file,$rate=100,$forceDownload=true)
    {
//        $file = './files/centos.zip';
//        $rate = 1000; //下载速度 单位 kb/s
//        downFile($file,$rate,true);

        if(!file_exists($file))
        {
            header("HTTP/1.1 404 Not Found");
            return false;
        }
        if(!is_readable($file)) {
            header("HTTP/1.1 404 Not Found");
            return false;
        }

        #读取文件的信息
        $fileStat = stat($file);
        $lastModified = $fileStat['mtime'];

        #拼成etag，防止文件发生修改
        $md5 = md5($fileStat['mtime'] . '=' . $fileStat['ino'] . '=' . $fileStat['size']);
        $etag = '"' . $md5 . '-' . crc32($md5) . '"';

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) {
            header("HTTP/1.1 304 Not Modified");
            return true;
        }

        if (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $lastModified) {
            header("HTTP/1.1 304 Not Modified");
            return true;
        }

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
            header("HTTP/1.1 304 Not Modified");
            return true;
        }


        $fileSize = $fileStat['size'];
        $contentLength = $fileSize;//文件大小
        $isPartial = false;//是否断点续传
        $fancyName = basename($file);

        //计算断点续传的开始位置
        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER['HTTP_RANGE'], $matches)) {
                $startPos = $matches[1];
                $endPos = $matches[2];

                if ($startPos == '' && $endPos == '') {
                    return false;
                }

                if ($startPos == '') {
                    $startPos = $fileSize - $endPos;
                    $endPos = $fileSize - 1;
                } else if ($endPos == '') {
                    $endPos = $fileSize - 1;
                }

                $startPos = $startPos < 0 ? 0 : $startPos;//开始位置
                $endPos = $endPos > $fileSize - 1 ? $fileSize - 1 : $endPos;//结束位置

                $length = $endPos - $startPos + 1;//剩余大小

                if ($length < 0) {
                    return false;
                }

                $contentLength = $length;
                $isPartial = true;
            }
        }
        //断点续传 记录下次下载的位置
        if ($isPartial) {
            header('HTTP/1.1 206 Partial Content');
            header(sprintf('Content-Range:bytes %s-%s/%s', $startPos, $endPos, $fileSize));

        } else {
            header("HTTP/1.1 200 OK");
            $startPos = 0;
            $endPos = $contentLength - 1;
        }
        //设置header头
        header('Pragma: cache');
        header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $lastModified) . ' GMT');
        header("ETag: $etag");
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . $contentLength);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        //对不同浏览器进行中文设置，避免下载导致文件名乱码
        if ($forceDownload) {
            //处理中文文件名
            $ua = $_SERVER["HTTP_USER_AGENT"];
            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . rawurlencode($fancyName) . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . rawurlencode($fancyName));
            } else {
                header('Content-Disposition: attachment; filename="' . $fancyName . '"');
            }
        }else
        {
            header("Content-Disposition: attachment; filename=" . $fancyName);
        }

        $bufferSize = 1024;//设置最小读取字节数 1kb
        //判断是否有设置下载速度
        if($rate > 0)
        {
            $bufferSize = $rate * $bufferSize; //100*1024 下载速度最大为100KB/s
        }

        $bytesSent = 0;
        $outputTimeStart = 0.00;
        $fp = fopen($file, "rb");
        fseek($fp, $startPos);

        while ($bytesSent < $contentLength && !feof($fp) && connection_status() == 0) {
            $readBufferSize = $contentLength - $bytesSent < $bufferSize ? $contentLength - $bytesSent : $bufferSize;
            $buffer = fread($fp, $readBufferSize);
            echo $buffer;
            //输出缓冲
            if(ob_get_level()>0)
            {
                ob_flush();
            }
            flush();
            $bytesSent += $readBufferSize;
            sleep(1); //睡眠一秒 这里也就是限制下载速度的关键 一秒只读$readBufferSize字节
        }
        if($fp) fclose($fp);
    }
}
