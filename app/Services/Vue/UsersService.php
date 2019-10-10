<?php

namespace App\Services\Vue;

use App\Models\Vue\UsersModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 用户模块 service
 * Class UsersService
 * @package App\Services\MiniWeChat
 */
class UsersService {

    /**
     * @var UsersModel
     */
    public $usersModel;

    /**
     * UsersService constructor.
     * @param UsersModel $usersModel
     */
    public function __construct(UsersModel $usersModel)
    {
        $this->usersModel = $usersModel;
    }

    /**
     * 获取用户信息
     * @param $username
     * @return mixed
     */
    public function getUserInfoByUserName($username)
    {
        return $this->usersModel->getUserInfoByUserName($username);
    }

    /**
     * 获取用户扩展信息
     * @param $userId
     * @return mixed
     */
    public function getUserExtendsInfoById($userId)
    {
        $res = $this->usersModel->getUserExtendsInfoById($userId);
        if (!$res) {
            return false;
        }
        $res->birthday = dateTimeStr($res->birthday);
        return $res;
    }

    /**
     * 用户列表
     * @param $params
     * @return array
     */
    public function getUserList($params)
    {
        $page = $params['page'] ? $params['page'] : 1;
        $limit = $params['limit'] ? $params['limit'] : 20;
        $sort = $params['sort'];

        $username = isset($params['username']) ? (!empty(trim($params['username'])) ? trim($params['username']) : '') : '';
        $role = isset($params['role']) ? $params['role'] : '';
        $status = isset($params['status']) ? $params['status'] : -1;

        $order_type = 'asc';
        if ($sort == '-id'){
            $order_type = 'desc';
        }

        $list = DB::table('vue_users');
        if (!empty($username)) {
            $list = $list->where('username','like','%'.$username.'%');
        }
        if (!empty($role)) {
            $list = $list->where('role',$role);
        }
        if (-1 != $status) {
            $list = $list->where('status',$status);
        }

        $data = array('items' => array(),'total' => 0);
        $total = $list->count();
        if (!$total || 0 == $total) {
            return $data;
        }
        // 获取list 集合
        $skip = ($page-1)*$limit;
        $list = $list->select('vue_users.id','vue_users.avatar_url','vue_users.username','vue_users.role','vue_users.create_at','vue_users.status','roles.name as role_name')
            ->leftJoin('roles','vue_users.role','roles.id')->orderBy('vue_users.id',$order_type)->skip($skip)->limit($limit)->get()->toArray();

        if (!$list) {
            return $data;
        }

        foreach ($list as $key=>$value) {
            $list[$key]->create_at = date('Y-m-d H:i',$value->create_at);
        }
        $data['items'] = $list;
        $data['total'] = $total;

        return $data;
    }

    /**
     * 添加用户
     * @param $params
     * @return int
     */
    public function addUser($params) {
        //默认密码 123456
        $password = password(123456);
        $params['password'] = $password['password'];
        $params['encrypt'] = $password['encrypt'];
        $params['update_at'] = time();
        try{
            $res = $this->usersModel->addUser($params);
        }catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
        return $res;
    }

    /**
     * 更新用户
     * @param $data
     * @return int
     */
    public function updateUser($data) {
        $where['id'] = $data['id'];
        if (isset($data['update_status'])) { //更新状态
            $update = array( 'status'=>$data['update_status']);
        }else{
            $update = array('role'=>$data['role'],'username'=>$data['username']);
        }
        $update['update_at'] =  time();
        try{
            $res = $this->usersModel->updateUser($where,$update);
        }catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
        return $res;
    }

    /**
     * 更新用户头像
     * @param $userId
     * @param $fileName
     * @return bool|int
     */
    public function updateAvatar($userId,$fileName) {
        try{
            $update['update_at'] =  time();
            $update['avatar_url'] =  $fileName;
            $where['id'] = $userId;
            $res = $this->usersModel->updateUser($where,$update);
        }catch (\Exception $e) {
            Log::info($e->getMessage());
            return false;
        }
        return $res;
    }

    /**
     * 更新用户扩展信息
     * @param $data
     * @return bool|int
     */
    public function updateUserInfoExtends($data) {

        $data['birthday'] = str_replace('-','',$data['birthday']);
        if (!$this->getUserExtendsInfoById($data['user_id'])) {
            return $this->usersModel->insertUserExtendInfo($data);
        }
        $where['user_id'] = $data['user_id'];
        unset($data['user_id']);
        try{
            $res = $this->usersModel->updateUserInfoExtends($where,$data);
        }catch (\Exception $e) {
            Log::info($e->getMessage());
            return false;
        }
        return $res;
    }
}
