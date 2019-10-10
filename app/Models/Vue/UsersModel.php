<?php
namespace App\Models\Vue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 用户模块 model
 * Class UsersModel
 * @package App\Models\Vue
 */
class UsersModel extends Model{

    /**
     * 用户表
     * @var string
     */
    public $user_table = 'vue_users';

    /**
     * 用户信息扩展表
     * @var string
     */
    public $user_extends_table = 'vue_users_extends';

    /**
     * 根据用户名获取用户信息
     * @param $username
     * @return int
     */
    public function getUserInfoByUserName($username){
        return DB::table($this->user_table)->select('id as user_id' ,'avatar_url','role','username','encrypt','password','status')
            ->where(array('username'=>$username))->first();
    }

    /**
     * 用户信息扩展表
     * @param $userId
     * @return array|Model|\Illuminate\Database\Query\Builder|mixed|null|object|\stdClass
     */
    public function getUserExtendsInfoById($userId){
        return DB::table($this->user_extends_table)->select('sex','birthday','mobile','province','city','district','nickname')
            ->where(array('user_id'=>$userId))->first();
    }

    /**
     * 添加用户
     * @param $params
     * @return int
     */
    public function addUser($params){
        return DB::table($this->user_table)->insertGetId($params);
    }

    /**
     * 更新用户
     * @param $where
     * @param $update
     * @return int
     */
    public function updateUser($where,$update) {
        return DB::table($this->user_table)->where($where)->update($update);
    }

    /**
     * 更新用户扩展信息
     * @param $where
     * @param $update
     * @return int
     */
    public function updateUserInfoExtends($where,$update) {
        return DB::table($this->user_extends_table)->where($where)->update($update);
    }

    /**
     * 添加用户扩展信息
     * @param $params
     * @return int
     */
    public function insertUserExtendInfo($params){
        return DB::table($this->user_extends_table)->insertGetId($params);
    }
}
