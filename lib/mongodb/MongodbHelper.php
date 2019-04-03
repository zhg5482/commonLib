<?php
namespace App\Lib\mongodb;
use Illuminate\Support\Facades\DB;

class MongodbHelper
{
    /**
     * @var 连接
     */
    public static $_conn;

    /**
     * @var 表
     */
    public $table;

    /**
     * MongodbHelper constructor.
     */
    public function __construct() {}

    /**
     * @return 连接|\Illuminate\Database\ConnectionInterface
     */
    public static function getInstance() {
        if (null == self::getInstance()) {
            self::$_conn = DB::connection('mongodb');
        }
        return self::$_conn;
    }
}
