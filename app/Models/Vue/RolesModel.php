<?php
namespace App\Models\Vue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 权限模块 model
 * Class RolesModel
 * @package App\Models\Vue
 */
class RolesModel extends Model{

    /**
     * @var string
     */
    public $role_table = 'roles';

    /**
     * @return array|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function getRoles() {
        return DB::table($this->role_table)->select('id','create_at','description','name','status','routes')
            ->where(array('status'=>1))->get();
    }

    /**
     * 获取用户权限
     * @param $id
     * @return array|Model|\Illuminate\Database\Query\Builder|mixed|null|object|\stdClass
     */
    public function getRoleById($id) {
        return DB::table($this->role_table)->select('routes_list')->where(array('id'=>$id))->first();
    }

    /**
     * 增加权限
     * @param $params
     * @return int
     */
    public function addRoles($params) {
        return DB::table($this->role_table)->insertGetId($params);
    }

    /**
     * 删除权限
     * @param $id
     * @return int
     */
    public function deleteRoles($id) {
        return DB::table('roles')->where(array('id'=>$id))->update(array('status'=>0,'update_at'=>time()));
    }

    /**
     * 更新权限
     * @param $id
     * @param $data
     * @return int
     */
    public function updateRoles($id,$data)  {
        return DB::table('roles')->where(array('id'=>$id))->update($data);
    }
}
