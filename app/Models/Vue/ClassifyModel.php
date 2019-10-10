<?php
namespace App\Models\Vue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 商品分类模块
 * Class ClassifyModel
 * @package App\Models\Vue
 */
class ClassifyModel extends Model{

    /**
     * @var string
     */
    public $classify_table = 'category';

    /**
     * @var string
     */
    public $goods_table = 'goods';

    /**
     * @var string
     */
    public $goods_picture_table = 'goods_picture';

    /**
     * 获取商品分类列表
     * @return array
     */
    public function getClassify() {
        return DB::table($this->classify_table)->select('category_id','category_logo','category_name','parent_id','category_status')
            ->get()->toArray();
    }

    /**
     * 更新分类信息状态
     * @param $where
     * @param $update_data
     * @return int
     */
    public function updateClassify($where,$update_data) {
        return DB::table($this->classify_table)->where($where)->update($update_data);
    }

    /**
     * 分类筛选
     * @return array
     */
    public function getClassifyList() {
        return DB::table($this->classify_table)->select('category_id','category_name')->where(array('parent_id' => 0))->get()->toArray();
    }

    /**
     * 添加分类
     * @param $params
     * @return int
     */
    public function addClassify($params) {
        return DB::table($this->classify_table)->insertGetId($params);
    }

    /**
     * 添加商品
     * @param $params
     * @return int
     */
    public function addGoodsInfo($params) {
        return DB::table($this->goods_table)->insertGetId($params);
    }

    /**
     * 更新商品状态
     * @param $where
     * @param $update
     * @return int
     */
    public function updateGoodsStatus($where,$update) {
        return DB::table($this->goods_table)->where($where)->update($update);
    }

    /**
     * 商品信息
     * @param $where
     * @return array|Model|\Illuminate\Database\Query\Builder|mixed|null|\stdClass
     */
    public function getGoodsInfoById($where) {
        return DB::table($this->goods_table)->select('goods_discount','goods_brand','goods_name','goods_price','goods_logo','category_id','goods_status','goods_desc')->where($where)->first();
    }

    /**
     * 商品图片信息
     * @param $where
     * @return array|Model|\Illuminate\Database\Query\Builder|mixed|null|object|\stdClass
     */
    public function getGoodsPicture($where) {
        return DB::table($this->goods_picture_table)->select('pic_url')->where($where)->first();
    }

    /**
     * 添加商品图片
     * @param $info
     * @return int
     */
    public function addGoodsPicture($info) {
        return DB::table($this->goods_picture_table)->insertGetId($info);
    }

    /**
     * 更新商品图片
     * @param $where
     * @param $info
     * @return int
     */
    public function updateGoodsPicture($where,$info) {
        return DB::table($this->goods_picture_table)->where($where)->update($info);
    }
}
