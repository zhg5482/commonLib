<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Lib\Cache\Manager;

/**
 * Class Controller
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var mixed
     */
    public $redis;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->redis = $this->getRedis();
    }

    /**
     * getRedis
     * @return mixed
     */
    private function getRedis()
    {
        if (!is_object($this->redis)) {
            $this->redis = Manager::getInstance([],'', 'redis');
        }
        return $this->redis;
    }
}
