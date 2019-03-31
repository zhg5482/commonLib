<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/3/31
 * Time: 上午11:45
 */

namespace App\Exceptions;

use Exception;

class ThrottleException extends Exception{
    protected $isReport = false;

    public function isReport(){
        return $this->isReport;
    }
}

