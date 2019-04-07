<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/4/4
 * Time: 下午6:51
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Lib\MongoDb\MongoDbHelper;

class NewsModel extends Model{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'news_channel';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'channel', 'channel_status','create_time','update_time','field_sort'];

    /**
     * mongo news字段
     * @var array
     */
    protected $_news_filelds = ['title','content','detail_url','source_title','channel','channel_id','image','create_time','type'];

    /**
     * @return mixed
     */
    public function getChannelsList(){
        return $this->select('id','channel')->where(array('channel_status'=>1))->orderBy('field_sort','desc')->get()->toArray();
    }

    /**
     * @param $channelType
     * @param $skip
     * @return mixed
     */
    public function getDataListByChannel($channelType,$skip) {
        return MongoDbHelper::getInstance()->getData('news',array('channel'=>$channelType),$this->_news_filelds,$skip);
    }

    /**
     * @param $search_key
     * @param $skip
     * @return mixed
     */
    public function getDataListBySearch($search_key,$skip) {
        return MongoDbHelper::getInstance()->getDataSearch('news','title',$search_key,$this->_news_filelds,$skip);
    }
}