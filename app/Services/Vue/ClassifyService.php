<?php
namespace App\Services\Vue;

use App\Models\Vue\ClassifyModel;
use Illuminate\Support\Facades\DB;

/**
 * 商品分类模块
 * Class ClassifyService
 * @package App\Services\Vue
 */
class ClassifyService {

    /**
     * @var
     */
    public $classifyModel;

    /**
     * ClassifyService constructor.
     * @param ClassifyModel $classifyModel
     */
    public function __construct(ClassifyModel $classifyModel)
    {
        $this->classifyModel = $classifyModel;
    }

    /**
     * 获取商品分类列表
     * @return array
     */
    public function getClassify() {
        return $this->classifyModel->getClassify();
    }

    /**
     * 更新分类信息状态
     * @param $params
     * @return bool
     */
    public function updateClassify($params) {
        $where['category_id'] = $params['id'];
        $update_data = array(
            'category_status' => $params['update_status'],
            'update_at' => time()
        );

        try{
           return $this->classifyModel->updateClassify($where,$update_data);
        }catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 更新分类信息
     * @param $params
     * @return bool|int
     */
    public function updateClassifyInfo($params) {
        $where['category_id'] = $params['category_id'];
        unset($params['category_id'],$params['category_status'],$params['create_at']);
        $params['update_at'] = time();
        try{
            return $this->classifyModel->updateClassify($where,$params);
        }catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 分类筛选列表
     * @return mixed
     */
    public function getClassifyList() {
        return $this->classifyModel->getClassifyList();
    }

    /**
     * 添加分类
     * @param $params
     * @return bool
     */
    public function addClassify($params) {
        try{
            $params['update_at'] = time();
            $params['create_at'] = time();
            $params['parent_id'] = isset($params['parent_id']) ? intval($params['parent_id']) : 0;
            return $this->classifyModel->addClassify($params);
        }catch (\Exception $e){
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * 获取分类联级
     */
    public function getClassifyJson() {
        $res = $this->classifyModel->getClassify();
        return $this->getClassifyTree($res);

    }

    /**
     * 树形分类
     * @param $data
     * @param int $parent_id
     * @return array
     */
    public function getClassifyTree($data,$parent_id = 0) {
        $res = array();
        foreach ($data as $k=>$val) {
            $single = array();
            if ($val->parent_id == $parent_id) {
                $single['value'] = $val->category_id;
                $single['label'] = $val->category_name;
                if ($children = $this->getClassifyTree($data,$val->category_id)) {
                    $single['children'] = $children;
                }
                $res[] = $single;
            }
        }
        return $res;
    }

    /**
     * 添加商品
     * @param $params
     * @return bool
     */
    public function addGoodsInfo($params) {
        $params['goods_status'] = $params['goods_status'] === false ? 2 : 1;
        $params['category_id'] = implode(',',$params['category_id']);
        $params['update_at'] = time();
        try{
            DB::beginTransaction();
            $goods_picture =$params['goods_picture'] ;
            unset($params['goods_picture']);
            if (isset($params['goods_id']) && intval($params['goods_id']) > 0) {
                $where['goods_id'] = $params['goods_id'];
                unset($params['goods_id']);
                $res = $this->classifyModel->updateGoodsStatus($where,$params);
                $goods_id = $where['goods_id'];
            }else {
                $params['create_at'] = time();
                $res = $this->classifyModel->addGoodsInfo($params);
                $goods_id = $res;
            }

            // todo :: 异步执行
            $this->addGoodsPicture($goods_picture,$goods_id);
            DB::commit();
        }catch (\Exception $e) {
            echo $e->getMessage();
            DB::rollBack();
            $res = false;
        }
        return $res;
    }

    /**
     * @param $goods_picture
     */
    public function addGoodsPicture($goods_picture,$goods_id) {
        //替换 url 中的链接为 response中的 avatar
        if ($goods_picture) {
            $goods_picture_info = $this->classifyModel->getGoodsPicture(array('goods_id'=>$goods_id));
            foreach ($goods_picture as $k=>$v) {
                $response = $v['response'];
                $goods_picture[$k]['url'] = $response['data']['avatar'];
            }
            $info['pic_url'] = json_encode($goods_picture);
            if ($goods_picture_info) {
                $this->classifyModel->updateGoodsPicture(array('goods_id'=>$goods_id),$info);
            }else{
                $info['goods_id'] = $goods_id;
                $info['create_at'] = time();
                $this->classifyModel->addGoodsPicture($info);
            }
        }
    }

    /**
     * 获取商品列表
     * @param $params
     * @return array
     */
    public function getGoodsList($params) {
        $page = $params['page'] ? $params['page'] : 1;
        $limit = $params['limit'] ? $params['limit'] : 20;

        $goods_name = isset($params['goods_name']) ? (!empty(trim($params['goods_name'])) ? trim($params['goods_name']) : '') : '';
        $category_id = isset($params['category_id']) ? $params['category_id'][count($params['category_id'])-1] : '';
        $goods_status = isset($params['goods_status']) ? $params['goods_status'] : -1;


        $list = DB::table('goods');
        if (!empty($goods_name)) {
            $list = $list->where('goods_name','like','%'.$goods_name.'%');
        }
        if (!empty($category_id)) {
            $list = $list->whereRaw('FIND_IN_SET(?,goods.category_id)', $category_id);
        }
        if (-1 != $goods_status) {
            $list = $list->where('goods_status',$goods_status);
        }

        $data = array('items' => array(),'total' => 0);
        $total = $list->count();
        if (!$total || 0 == $total) {
            return $data;
        }
        // 获取list 集合
        $skip = ($page-1)*$limit;
        $list = $list->select('goods.goods_id','goods.goods_name','goods.goods_price','goods.goods_logo','goods.goods_status','goods.goods_brand')
            ->skip($skip)->limit($limit)->get()->toArray();

        if (!$list) {
            return $data;
        }

        $data['items'] = $list;
        $data['total'] = $total;

        return $data;
    }

    /**
     * 更新状态
     * @param $params
     * @return bool
     */
    public function updateGoodsStatus($params) {
        $where['goods_id'] = $params['goods_id'];
        $update_data = array(
            'goods_status' => $params['goods_status'],
            'update_at' => time()
        );

        try{
            return $this->classifyModel->updateGoodsStatus($where,$update_data);
        }catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * 获取单条商品信息
     * @param $id
     * @return mixed
     */
    public function getGoodsInfoById($id) {
        $where['goods_id'] = $id;
        $res = $this->classifyModel->getGoodsInfoById($where);
        $goods_picture = $this->classifyModel->getGoodsPicture($where);
        $category_arr = array();
        foreach (explode(',',$res->category_id) as $value) {
            $category_arr[] = intval($value);
        }
        $res->category_id = $category_arr;
        if ($goods_picture) {
            $res->goods_picture = json_decode($goods_picture->pic_url,true);
        }
        return $res;
    }
}
