<?php

namespace App\Http\Controllers\Vue;

use App\Http\Controllers\Controller;
use App\Lib\Email\SendEmail;
use App\Services\Vue\UsersService;
use Illuminate\Http\Request;
use App\Lib\FastDfs\FastDfsHelper;
use Illuminate\Support\Facades\Log;

/**
 * 用户模块 controller
 * Class UsersController
 * @package App\Http\Controllers\Vue
 */
class UsersController extends Controller
{
    /**
     * 用户状态过期时间
     */
    const PASSPORT_EXPIRE_TIME = 2592000;

    /**
     * @var
     */
    public $usersService;

    /**
     * UsersController constructor.
     * @param Request $request
     * @param UsersService $usersService
     */
    public function __construct(Request $request,UsersService $usersService)
    {
        parent::__construct($request);
        $this->usersService = $usersService;
    }

    /**
     * vue登录
     */
    public function login() {
        $username = trim($this->request->input('username',''));
        $password = trim($this->request->input('password',''));
        if (empty($username) || empty($password)) {
            echoToJson('No authority',array());
        }

        if (!is_email($username)) {
            echoToJson('No authority',array());
        }

        $userInfo = $this->usersService->getUserInfoByUserName($username);
        if (!$userInfo) { //用户不存在
            echoToJson('No authority',array());
        }
        $password2 = password($password,$userInfo->encrypt);
        if ($userInfo->password != $password2) {//密码错误
            echoToJson('No authority',array());
        }

        if ($userInfo->status != 1) { //用户未激活 或已冻结
            echoToJson('No authority',array());
        }

        // 获取用户扩展信息
        $userInfo_extends = $this->usersService->getUserExtendsInfoById($userInfo->user_id);
        if ($userInfo_extends) {
            $userInfoArr = (array)$userInfo;
            unset($userInfoArr['status'],$userInfoArr['password'],$userInfoArr['encrypt']);
            $userInfo = (object)array_merge($userInfoArr,(array)$userInfo_extends);
        }
        $userInfo->client_ip = $this->request->getClientIp();
        // redis 保存用户信息
        if (!$this->redis->setex('passport_'.$userInfo->user_id, self::PASSPORT_EXPIRE_TIME, json_encode($userInfo))) {
            echoToJson('No authority',array());
        }
        $token = authcode($userInfo->user_id, 'ENCODE');
        echoToJson('Default code',array('token'=>$token));
    }

    /**
     * 获取用户信息
     * @return bool
     */
    public function info() {
        $token = $this->request->headers->get("X-Token");
        $user_id = authcode($token, 'DECODE');
        $userInfo = $this->redis->get('passport_'.$user_id);
        if (!$userInfo) {
            return false;
        }
        echoToJson('Default code',$userInfo);
    }

    /**
     * 退出登录
     */
    public function logout() {
        $token = $this->request->headers->get("X-Token");
        $user_id = authcode($token, 'DECODE');
        $this->redis->delete('passport_'.$user_id);
        echoToJson('Default code',array());
    }

    /**
     * 用户列表
     */
    public function getUserList() {
        $data = $this->usersService->getUserList($this->request->input());
        echoToJson('Default code',$data);
    }

    /**
     * 添加用户
     */
    public function addUser(){
        $data = $this->request->input();
        if (empty($data['username']) || !is_email($data['username'])) //邮箱格式有误 或不存在
        {
            echoToJson('No authority',array());
        }
        $data['create_at'] = time();

        $userInfo = $this->usersService->getUserInfoByUserName($data['username']);
        if ($userInfo) { //账号已存在
            echoToJson('No authority',array());
        }
        $res = $this->usersService->addUser($data);
        if (!$res) {
            echoToJson('No authority',array());
        }
        //todo:: 这块后面需添加到队列中
        $address = array(array('address'=>$data['username'],'nickname'=>'激活用户'));
        $res = SendEmail::getInstance()->mailer($address, '激活邮件', '激活url') ;
        if (!$res) {
            Log::info("发送邮件失败");
        }

        $data['id'] = $res;
        echoToJson('Default code',$data);
    }

    /**
     * 用户更新
     */
    public function updateUser(){
        $data = $this->request->input();
        if (empty($data)) {
            echoToJson('No authority',array());
        }
        if (!isset($data['id'])) {
            echoToJson('No authority',array());
        }
        // todo:: 更新redis 用户信息
        $res = $this->usersService->updateUser($data);
        if (!$res) {
            echoToJson('No authority',array());
        }
        echoToJson('Default code',$res);
    }

    /**
     * 更新用户头像
     */
    public function updateAvatar() {
        $file = $_FILES['avatar'];
        $userId = $this->request->input('userId','');

        if (!$userId) {
            echoToJson('No authority',array());
        }
        $ret = FastDfsHelper::getInstance()->uploadFile($file);
        if (!$ret) {
            echoToJson('No authority',array());
        }
        $fileName = config('app')['fileUrl'].'/'.$ret['group_name'].'/'.$ret['filename'];
        //todo:: 更新redis 用户信息
        $res = $this->usersService->updateAvatar($userId,$fileName);
        if (!$res) {
            echoToJson('No authority',array());
        }
        echoToJson('Default code',array('avatar'=>$fileName));
    }

    /**
     * 获取用户扩展信息
     * @param $id
     */
    public function getUserInfoExtends($id) {
        if (!$id) {
            echoToJson('No authority',array());
        }
        $res = $this->usersService->getUserExtendsInfoById($id);
        echoToJson('Default code',$res);
    }

    /**
     * 更新用户扩展信息
     */
    public function updateUserInfoExtends() {
        $data = $this->request->input();
        if (empty($data)) {
            echoToJson('No authority',array());
        }
        if (!isset($data['user_id'])) {
            echoToJson('No authority',array());
        }
        // todo:: 更新redis 用户信息
        $res = $this->usersService->updateUserInfoExtends($data);
        if (!$res) { //更新失败
            echoToJson('No authority',array());
        }
        echoToJson('Default code',$res);
    }
}
