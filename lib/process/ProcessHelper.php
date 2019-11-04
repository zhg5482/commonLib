<?php
namespace App\Lib\Process;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * 进程
 * Class ProcessHelper
 * @package App\Lib\Process
 */
class ProcessHelper {

    private static $_instance = null;

    private static $process;

    private function __construct()
    {
    }

    /**
     * @param $cmd
     * @return null|ProcessHelper
     */
    public static function getInstance($cmd) {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        self::$process = new Process($cmd);
        return self::$_instance;
    }

    /**
     * cmd 执行
     * @return bool
     */
    public function runProcess() {
        try {
            self::$process->mustRun();
            return self::$process->getOutput();
        } catch (ProcessFailedException $e) {
            return false;
        }
    }

    /**
     * 实时process 输出
     */
    public function realTimeProcessStream() {
        self::$process->run(function ($type, $buffer) {
            if (Process::OUT === $type) {
                echo "\nRead from stdout: ".$buffer;
            } else { // $process::ERR === $type
                echo "\nRead from stderr: ".$buffer;
            }
        });
    }
}
