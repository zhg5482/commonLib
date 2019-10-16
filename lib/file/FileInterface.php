<?php
namespace App\Lib\File;

/**
 * Interface FileInterface
 * @package App\Lib\File
 */
interface FileInterface {

    /**
     * 获取文件扩展名
     * @param $fileName
     * @return mixed
     */
    function getFileExtension($fileName);

    /**
     * 下载文件
     * @param $fileName
     * @return mixed
     */
    function downFile($fileName);

    /**
     * 上传文件
     * @param $fileName
     * @return mixed
     */
    function uploadFile($fileName);
}
