<?php
namespace App\Models\Vue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 系统模块 model
 * Class SystemsModel
 * @package App\Models\Vue
 */
class SystemsModel extends Model{

    /**
     * @var string
     */
    public $message_table = 'messages';

    /**
     * 获取最新消息数量
     * @param $id
     * @return int
     */
    public function getNewMessagesNum($id) {
        return DB::table($this->message_table)->where(array('status'=>0,'user_id'=>$id))->count();
    }

    /**
     * 获取消息列表
     * @param $id
     * @param $status
     * @return array
     */
    public function getMessagesList($id) {
        return DB::table($this->message_table)->select('id','title','status','create_at','type')
            ->whereIn('status',[0,1,2])->where(array('user_id'=>$id))->get()->toArray();
    }

    /**
     * 更新消息状态
     * @param $ids
     * @param $status
     * @return int
     */
    public function updateMessageStatus($ids,$status) {
        return DB::table($this->message_table)->whereIn('id',$ids)->update(array('status'=>$status));
    }
}
