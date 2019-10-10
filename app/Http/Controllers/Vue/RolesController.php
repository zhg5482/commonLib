<?php

namespace App\Http\Controllers\Vue;

use App\Http\Controllers\Controller;
use App\Services\Vue\RolesService;
use Illuminate\Http\Request;
/**
 * 权限 controller
 * Class RolesController
 * @package App\Http\Controllers\Vue
 */
class RolesController extends Controller
{

    /**
     * @var RolesService
     */
    public $rolesService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request,RolesService $rolesService)
    {
        parent::__construct($request);
        $this->rolesService = $rolesService;
    }

    /**
     * roles权限
     */
    public function roles() {
        $data = $this->rolesService->getRoles();
        echoToJson('Default code',$data);
    }

    /**
     * 获取用户权限
     * @param $id
     */
    public function getRoleById($id) {
        $data = $this->rolesService->getRoleById($id);
        echoToJson('Default code',$data);
    }

    /**
     * 添加权限
     */
    public function addRoles() {
        $name = $this->request->input('name');
        $description = $this->request->input('description');
        $routes = $this->request->input('routes');
        if (empty($name) ||empty($description) ||empty($routes) ) {
            echoToJson('No authority',array());
        }
        $res = $this->rolesService->addRoles($this->request->input());
        echoToJson('Default code',$res);
    }

    /**
     * 删除权限
     * @param $id
     */
    public function deleteRoles($id) {
        if (empty($id)  ) {
            echoToJson('No authority',array());
        }
        $res = $this->rolesService->deleteRoles($id);
        echoToJson('Default code',$res);
    }

    /**
     * 更新权限
     * @param $id
     */
    public function updateRoles($id) {
        $name = $this->request->input('name');
        $description = $this->request->input('description');
        $routes = $this->request->input('routes');

        if (empty($name) || empty($id) ||empty($description) ||empty($routes) ) {
            echoToJson('No authority',array());
        }
        $res = $this->rolesService->updateRoles($id,$this->request->input());
        echoToJson('Default code',$res);
    }
}
