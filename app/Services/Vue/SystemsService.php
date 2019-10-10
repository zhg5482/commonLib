<?php
namespace App\Services\Vue;

use App\Models\Vue\SystemsModel;

/**
 * 系统模块 service
 * Class SystemsService
 * @package App\Services\MiniWeChat
 */
class SystemsService {

    /**
     * @var SystemsModel
     */
    public $systemsModel;

    /**
     * SystemsService constructor.
     * @param SystemsModel $systemsModel
     */
    public function __construct(SystemsModel $systemsModel)
    {
        $this->systemsModel = $systemsModel;
    }

    /**
     * 获取最新消息数量
     * @param $id
     * @return mixed
     */
    public function getNewMessagesNum($id) {
        return $this->systemsModel->getNewMessagesNum($id);
    }

    /**
     * 获取消息列表
     * @param $id
     * @param $status
     * @return array
     */
    public function getMessagesList($id) {
        $res = $this->systemsModel->getMessagesList($id);
        $data = array(
            'unread'=>[],
            'read'=>[],
            'recycle'=>[],
        );
        if (!$res) {
            return $data;
        }

        foreach ($res as $k=>$v) {
            $v->create_at = date('Y-m-d H:i',$v->create_at);
            $v->title = $v->type == 99 ?  '[系统消息]'.$v->title : $v->title;
            if ($v->status == 0) {
                $data['unread'][] = $v;
            }else if($v->status == 1) {
                $data['read'][] = $v;
            }else{
                $data['recycle'][] = $v;
            }
        }
        return $data;
    }

    /**
     * 更新消息状态
     * @param $ids
     * @param $status
     * @return int
     */
    public function updateMessageStatus($ids,$status) {
        return $this->systemsModel->updateMessageStatus($ids,$status);
    }
}
