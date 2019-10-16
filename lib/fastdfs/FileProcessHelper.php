<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/10/16
 * Time: 上午10:55
 */
namespace App\Lib\FastDfs;
header('Content-type:text/html;Charset=UTF-8');

class FileProcessHelper {

    /**
     * 分片大小
     * @var float|int
     */
    private $blockSize = 4*1024*1024;

    /**
     * @var
     */
    private static $instance;

    /**
     * FFmPegHelper constructor.
     */
    private function __construct(){}

    /**
     * @return FileProcessHelper
     */
    public static function getInstance() {
        if (null == self::$instance) {
            self::$instance =new self();
        }
        return self::$instance;
    }

    /**
     * 获取文件hashcode
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
        $blkcnt   = $fileSize/$this->blockSize;
        if ($fileSize % $this->blockSize) $blkcnt += 1;
        // 把数据装入一个二进制字符串
        $buffer .= pack("L", $blkcnt);
        if ($fileSize <= $this->blockSize) {
            $content = fread($f, $this->blockSize);
            if (!$content) {
                fclose($f);
                exit("read file error");
            }
            $sha .= sha1($content, TRUE);
        } else {
            for($i=0; $i<$blkcnt; $i+=1) {
                $content = fread($f, $this->blockSize);
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
        return $hash;
    }

    /**
     * 文件内容过滤
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
     * 获取线上文件大小
     * @param $url 文件地址
     * @return bool|string
     */
    public function get_http_file_size($url) {
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
    public function thumb($src_path,$max_w,$max_h,$prefix = 'sm_',$flag = true) {
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
     * 下载文件[当前服务器本地文件]
     * @param $file 文件
     * @param int $rate 下载速度 kb/s
     * @param bool $forceDownload 是否中文设置
     * @return bool
     */
    function downFile($file,$rate=100,$forceDownload=true)
    {
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
