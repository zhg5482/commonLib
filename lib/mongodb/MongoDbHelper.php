<?php
namespace App\Lib\MongoDb;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

class MongoDbHelper
{
    /**
     * @var
     */
    private static $_instances;

    /**
     * @var 连接
     */
    private static $_conn;

    /**
     * @var db
     */
    private $db;

    /**
     * @var 表
     */
    private $table;

    /**
     * 默认分页数
     * @var int
     */
    private $page_num = 30;

    /**
     * MongoDbHelper constructor.
     */
    public function __construct() {}

    /**
     * @return 连接|\Illuminate\Database\ConnectionInterface
     */
    public static function getInstance($db = 'mongodb') {
        $class = get_class();
        if (empty(self::$_instances)) {
            self::$_instances = new $class();
        }
        self::$_conn = DB::connection($db);
        return self::$_instances;
    }

    /**
     * @param $table
     * @param $where
     * @param string $fields
     * @param int $skip
     * @param string $order_field
     * @param string $order_type
     * @return mixed
     */
    public function getData($table,$where,$fields='*',$skip=0,$order_field='create_time',$order_type='desc'){
        return self::$_conn->table($table)
            ->select($fields)
            ->where($where)
            ->orderBy($order_field,$order_type)
            ->skip($skip)
            ->take($this->page_num)
            ->get()
            ->toArray();
    }

    /**
     * @param $table
     * @param $search_key
     * @param $search_val
     * @param string $fields
     * @param int $skip
     * @param string $order_field
     * @param string $order_type
     * @return mixed
     */
    public function getDataSearch($table,$search_key,$search_val,$fields='*',$skip=0,$order_field='create_time',$order_type='desc'){
        return self::$_conn->table($table)
            ->select($fields)
            ->where($search_key,'like','%'.$search_val.'%')
            ->orderBy($order_field,$order_type)
            ->skip($skip)
            ->take($this->page_num)
            ->get()
            ->toArray();
    }
}
