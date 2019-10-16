<?php
namespace App\Lib\File;

/**
 * Class FileAbstract
 * @package App\Lib\File
 */
Abstract class FileAbstract implements FileInterface {

    protected $file_name;

    public function getFileExtension($fileName)
    {
        // TODO: Implement getFileExtension() method.
        return pathinfo($fileName,PATHINFO_EXTENSION);
    }

    public function downFile($fileName)
    {
        // TODO: Implement downFile() method.
    }

    public function uploadFile($fileName)
    {
        // TODO: Implement uploadFile() method.
    }
}
