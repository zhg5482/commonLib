<?php
namespace App\Lib\Process;

use Symfony\Component\Filesystem\Filesystem;

class FileSystemHelper {
    private static $_instance = null;

    private static $fileSystem;

    private function __construct()
    {
    }

    public function getInstances() {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }
        self::$fileSystem = new Filesystem();
        return self::$_instance;
    }
}
