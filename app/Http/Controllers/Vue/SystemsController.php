<?php

namespace App\Http\Controllers\Vue;

use App\Http\Controllers\Controller;
use App\Services\Vue\SystemsService;
use Illuminate\Http\Request;

/**
 * 系统模块 controller
 * Class SystemsController
 * @package App\Http\Controllers\Vue
 */
class SystemsController extends Controller
{

    /**
     * @var SystemsService
     */
    public $systemService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request,SystemsService $systemsService)
    {
        parent::__construct($request);
        $this->systemService = $systemsService;
    }

    /**
     * 获取最新消息数量
     * @param $id
     */
    public function getNewMessagesNum($id) {
        if (!$id) {
            echoToJson('Request success',array());
        }
        $message_count = $this->systemService->getNewMessagesNum($id);
        echoToJson('Default code',array('message_count'=>$message_count));
    }

    /**
     * 获取消息列表
     * @param $id
     */
    public function getMessagesList($id) {
        if (!$id) {
            echoToJson('Request success',array());
        }
        $list = $this->systemService->getMessagesList($id);
        echoToJson('Default code',$list);
    }

    /**
     * 更新消息状态
     */
    public function updateMessageStatus() {
        $ids = $this->request->input('ids','');
        $status = $this->request->input('status',-1);
        if (empty($ids) || -1 == $status || !in_array($status,array(0,1,2,99))) {
            echoToJson('Request success',array());
        }
        $res = $this->systemService->updateMessageStatus($ids,$status);
        if (!$res) {
            echoToJson('Request success',array());
        }
        echoToJson('Default code',array());
    }
}
