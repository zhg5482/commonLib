<?php

namespace App\Http\Controllers\Vue;

use App\Http\Controllers\Controller;
use App\Services\Vue\ClassifyService;
use Illuminate\Http\Request;

/**
 * 商品分类模块
 * Class ClassifyController
 * @package App\Http\Controllers\Vue
 */
class ClassifyController extends Controller
{

    /**
     * @var
     */
    public $classifyService;

    /**
     * ClassifyController constructor.
     * @param Request $request
     * @param ClassifyService $classifyService
     */
    public function __construct(Request $request,ClassifyService $classifyService)
    {
        $this->classifyService = $classifyService;
        parent::__construct($request);
    }

    /**
     * 获取商品分类列表
     */
    public function getClassify() {
        $res = $this->classifyService->getClassify();
        echoToJson('Default code',$res);
    }

    /**
     * 更新分类信息状态
     */
    public function updateClassify() {
        $id = intval($this->request->input('id', -1));
        if (-1 == $id) {
            echoToJson('No authority',array());
        }
        $res = $this->classifyService->updateClassify($this->request->input());
        if (!$res) {
            echoToJson('No authority',array());
        }
        echoToJson('Default code',$res);
    }

    /**
     * 更新分类信息
     * @param $id
     */
    public function updateClassifyInfo($id) {

        if (!$id) {
            echoToJson('No authority',array());
        }
        $res = $this->classifyService->updateClassifyInfo($this->request->input());
        if (!$res) {
            echoToJson('No authority',array());
        }
        echoToJson('Default code',$res);
    }

    /**
     * 分类选择列表
     */
    public function getClassifyList() {
        $res = $this->classifyService->getClassifyList();
        echoToJson('Default code',$res);
    }

    /**
     * 添加分类
     */
    public function addClassify() {
        $classify_name = $this->request->input('category_name');
        if (empty($classify_name) ) {
            echoToJson('No authority',array());
        }
        $res = $this->classifyService->addClassify($this->request->input());
        if (!$res) {
            echoToJson('No authority',array());
        }
        echoToJson('Default code',$res);
    }

    /**
     * 获取分类基联
     */
    public function getClassifyJson() {
        $res = $this->classifyService->getClassifyJson();
        echoToJson('Default code',$res);
    }

    /**
     * 添加商品
     */
    public function addGoodsInfo() {
        $goods_name = $this->request->input('goods_name','');
        $goods_price = $this->request->input('goods_price','');
        $category_id = $this->request->input('category_id','');
        $goods_desc = $this->request->input('goods_desc','');
        if (empty($goods_name) ||empty($goods_price) ||empty($category_id) ||empty($goods_desc)){
            echoToJson('No authority',array());
        }
        $res = $this->classifyService->addGoodsInfo($this->request->input());

        if (!$res) {
            echoToJson('No authority',array());
        }
        echoToJson('Default code',$res);
    }

    /**
     * 获取商品列表
     */
    public function getGoodsList() {
        $res = $this->classifyService->getGoodsList($this->request->input());
        echoToJson('Default code',$res);
    }

    /**
     * 更新商品状态
     */
    public function updateGoodsStatus() {
        $goods_id = $this->request->input('goods_id', -1);
        if(-1 == $goods_id) {
            echoToJson('No authority',array());
        }
        $res = $this->classifyService->updateGoodsStatus($this->request->input());
        if (!$res) {
            echoToJson('No authority',array());
        }
        echoToJson('Default code',$res);
    }

    /**
     * 获取单条商品信息
     * @param $id
     */
    public function getGoodsInfoById($id) {
        if (!$id) {
            echoToJson('No authority',array());
        }
        $res = $this->classifyService->getGoodsInfoById($id);
        if (!$res) {
            echoToJson('No authority',array());
        }
        echoToJson('Default code',$res);
    }
}
