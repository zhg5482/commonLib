<?php
namespace App\Lib\MongoDb;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

class MongoDbHelper
{
    /**
     * @var 连接
     */
    public static $_conn;

    /**
     * @var db
     */
    public $db;

    /**
     * @var 表
     */
    public $table;

    /**
     * MongoDbHelper constructor.
     */
    public function __construct() {}

    /**
     * @return 连接|\Illuminate\Database\ConnectionInterface
     */
    public static function getInstance() {
        if (! self::$_conn ) {
            self::$_conn = DB::connection('mongodb');
        }
        return self::$_conn;
    }
}
