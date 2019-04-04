<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/4/4
 * Time: 下午6:51
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

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
     * @return mixed
     */
    public function getChannelsList(){
        return $this->select('id','channel')->where(array('channel_status'=>1))->orderBy('field_sort','desc')->get()->toArray();
    }

}