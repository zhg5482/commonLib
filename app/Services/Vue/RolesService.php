<?php

namespace App\Services\Vue;

use App\Models\Vue\RolesModel;

/**
 * 权限 service
 * Class UsersService
 * @package App\Services\MiniWeChat
 */
class RolesService {

    /**
     * @var RolesModel
     */
    public $rolesModel;

    /**
     * RolesService constructor.
     * @param RolesModel $rolesModel
     */
    public function __construct(RolesModel $rolesModel)
    {
        $this->rolesModel = $rolesModel;
    }

    /**
     * @return array|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function getRoles() {
        $data = $this->rolesModel->getRoles();
        if (!$data) {
            return array();
        }
        foreach ($data as $key=>$val) {
            $data[$key]->routes = json_decode($data[$key]->routes ,true);
            $data[$key]->create_at = date('Y-m-d H:i:s',$data[$key]->create_at);
        }
        return $data;
    }

    /**
     * 获取用户权限
     * @param $id
     * @return mixed
     */
    public function getRoleById($id) {
        $data = $this->rolesModel->getRoleById($id);
        return json_decode($data->routes_list ,true);
    }

    /**
     * 增加用户权限
     * @param $params
     * @return mixed
     */
    public function addRoles($params) {
        unset($params['key']);
        $this->getRoute($params['routes_list'],$params['routes']);
        $params['routes'] = json_encode($params['routes']);
        $params['routes_list'] = json_encode($params['routes_list']);
        $params['create_at'] = time();
        $params['update_at'] = time();
        return $this->rolesModel->addRoles($params);
    }

    /**
     * 获得路由
     * @param $res
     * @param $route_list
     * @param string $parentPath
     */
    function getRoute(&$res,$route_list,$parentPath='') {
        foreach ($route_list as $key => $val) {
            if (isset($val['children'])) {
                $this->getRoute($res,$val['children'],$val['path'].'/');
            }
            if (false !== stripos($val['path'],$parentPath)) {
                $res[] = $val['path'];
            }else {
                $res[] = $parentPath.$val['path'];
            }
        }
    }

    /**
     * 删除权限
     * @param $id
     * @return int
     */
    public function deleteRoles($id) {
        return $this->rolesModel->deleteRoles($id);
    }

    /**
     * 更新权限
     * @param $id
     * @param $params
     * @return int
     */
    public function updateRoles($id,$params)  {
        unset($params['key'],$params['create_at']);
        $this->getRoute($params['routes_list'],$params['routes']);
        $params['routes'] = json_encode($params['routes']);
        $params['routes_list'] = json_encode($params['routes_list']);
        $params['update_at'] = time();
        return $this->rolesModel->updateRoles($id,$params) ;
    }
}
