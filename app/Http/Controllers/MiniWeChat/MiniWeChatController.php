<?php

namespace App\Http\Controllers\MiniWeChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;

class MiniWeChatController extends Controller
{
    /**
     * @var
     */
    public $newService;

    /**
     * @var \Illuminate\Foundation\Application|\Laravel\Lumen\Application|mixed
     */
    public $curlHandle;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->curlHandle = app('App\Lib\CurlHandle');
    }

    /**
     * 小程序登录
     */
    public function login() {
        $code = $this->request->input('code','');
        if (!$code) {
            echoToJson('No authority',array());
        }

        $appid = 'wxbcbfa7ef5882a46f';
        $secret = '3f3fae8f134c29b2a961b7b14b0bf213';
        $url = sprintf("https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code", $appid, $secret,$code);

        $wechat_info = $this->curlHandle->run($url);
        //"'{"":"C5r+xpWrduD9fkI7H1LuLw==","openid":"oI7jX5fi1cC4Ao5780Uk4erYcWZM"}'{"code":"0","message":"\u672a\u77e5\u64cd\u4f5c","result":{"3rd_session":"dcc3349a921c27dac0234ad258820134"}}"

        $wechat_info = json_decode($wechat_info,true);

        //todo:: 中间件数据效验 sign (时间戳 数据 随机串 加密 )
        $data = array(
            'third_Session' => md5($wechat_info['session_key'].$wechat_info['openid'].time())
        );
        //todo :: redis 保存 third_Session
        Redis::set($data['third_Session'], $wechat_info['session_key'].'-'.$wechat_info['openid']);

        echoToJson('Default code',$data);
    }

    /**
     * 用户信息添加更新
     */
    public function userAdd() {
        $third_Session = $this->request->input('third_Session','');
        if (empty($third_Session)) {
            echoToJson('No authority',array());
        }
        $openid_session_key = Redis::get($third_Session);
        $openid_session_key_arr = explode('-',$openid_session_key);
        if (! ($openId = $openid_session_key_arr[1])) {
            echoToJson('No authority',array());
        }
        $session_key = $openid_session_key_arr[0];
        $data = $this->request->input();
        $data['openId'] = $openId;
        $data['session_key'] = $session_key;
        $data['update_time'] = time();
        $data['status'] = 1;

        //todo::异步 消息队列

        $userInfo = DB::table('user')->where(array('openId'=>$openId))->first();
        if (!$userInfo) {
            $data['create_time'] = time();
            $res = DB::table('user')->insert($data);
        }else{
            $res = DB::table('user')->where(array('openId'=>$openId))->update($data);
        }

        if (!$res) {
            echoToJson('No authority',array());
        }else{
            echoToJson('Default code',array('code'=>0));
        }
    }


    /**
     * 订单信息
     */
    public function order() {
        $order_type = $this->request->input('order_type', -1);
        if (-1 == $order_type || !in_array($order_type,array(0,1,2,3)) ) {
            echoToJson('No authority',array());
        }

        $data = array();
        switch ($order_type) {
            case 0:
                $data = array(
                    array('name'=>'跃动体育运动俱乐部(圆明园店)','state'=>"待付款", 'time'=>"2018-09-30 14:00-16:00",'status'=>"未开始", 'image_url'=> "https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132", 'money'=>"132"),
                    array('name'=>'跃动体育运动俱乐部(圆明园店)','state'=>"待付款", 'time'=>"2018-10-12 18:00-20:00",'status'=>"未开始", 'image_url'=> "https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132", 'money'=>"205"),
                );
                break;
            case 1:
                $data = array(
                    array('name'=>'跃动体育运动俱乐部(圆明园店)','state'=>"交易成功", 'time'=>"2018-09-30 14:00-16:00",'status'=>"已结束", 'image_url'=> "https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132", 'money'=>"132"),
                    array('name'=>'跃动体育运动俱乐部(圆明园店)','state'=>"交易成功", 'time'=>"2018-10-12 18:00-20:00",'status'=>"已结束", 'image_url'=> "https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132", 'money'=>"205"),
                );
                break;
            case 2:
                $data = array(
                    array('name'=>'跃动体育运动俱乐部(圆明园店)','state'=>"待评价", 'time'=>"2018-09-30 14:00-16:00",'status'=>"已结束", 'image_url'=> "https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132", 'money'=>"132"),
                    array('name'=>'跃动体育运动俱乐部(圆明园店)','state'=>"待评价", 'time'=>"2018-10-12 18:00-20:00",'status'=>"未开始", 'image_url'=> "https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132", 'money'=>"205"),
                );
                break;
            case 3:
                $data = array(
                    array('name'=>'跃动体育运动俱乐部(圆明园店)','state'=>"已退换", 'time'=>"2018-09-30 14:00-16:00",'status'=>"已结束", 'image_url'=> "https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132", 'money'=>"132"),
                    array('name'=>'跃动体育运动俱乐部(圆明园店)','state'=>"已退换", 'time'=>"2018-10-12 18:00-20:00",'status'=>"已结束", 'image_url'=> "https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132", 'money'=>"205"),
                );
                break;
        }
        echoToJson('Default code',$data);
    }

    /**
     * 分类
     */
    public function classify() {
        $res = DB::table('category')->select('category_id','category_logo','category_name','parent_id','category_status')
            ->get()->toArray();
        $data = $this->getClassifyTree($res);
        echoToJson('Default code',$data);
    }

    public function getClassifyTree($data,$parent_id = 0) {
        $res = array();
        foreach ($data as $k=>$val) {
            $single = array();
            if ($val->parent_id == $parent_id) {
                $single['id'] = $val->category_id;
                $single['name'] = $val->category_name;
                $single['ishaveChild'] = false;
                $single['imgUrl'] = $val->category_logo;
                if ($children = $this->getClassifyTree($data,$val->category_id)) {
                    $single['ishaveChild'] = true;
                    $single['shopClassifyDtoList'] = $children;

                }
                $res[] = $single;
            }
        }
        return $res;
    }

    /**
     * 首页NavBar
     */
    public function homeNavBar() {
        $res = DB::table('category')->select('category_id','category_name')
            ->where(array('parent_id'=>0,'category_status'=>1))->get()->toArray();
        echoToJson('Default code',$res);
    }

    public function homeBanners() {
        $res = DB::table('goods')->select('goods_id','goods_logo')
            ->where(array('goods_status'=>1))->orderBy('goods_id','desc')->limit(5)->get()->toArray();
        echoToJson('Default code',$res);
    }

    public function activityBrands() {
        $data = array(
            array('typeId'=>1,'name'=>'一级标签','newprice'=>123,'imageurl'=>'https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132'),
            array('typeId'=>2,'name'=>'个耳光','newprice'=>232,'imageurl'=>'https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132'),
            array('typeId'=>3,'name'=>'如歌好听人','newprice'=>34,'imageurl'=>'https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132'),
            array('typeId'=>4,'name'=>'如桃花如图','newprice'=>99,'imageurl'=>'https://wx.qlogo.cn/mmopen/vi_32/BR5EhqRicJHN4Sr7iakAnXJhppSKj6VGx3HkbrfNJqRky762W2CYOr3ww9eLhKHYs1ubMH4p5ricM4XAGpsYDsm7w/132'),
        );
        echoToJson('Default code',$data);
    }

    public function getHotGoodsList()
    {
        $page = $this->request->input('page') ? $this->request->input('page') : 1;
        $limit = $this->request->input('size') ? $this->request->input('size') : 10;

        $category_id = $this->request->input('category_id')  ? $this->request->input('category_id') : '';

        $list = DB::table('goods');
        if (!empty($category_id)) {
            $list = $list->whereRaw('FIND_IN_SET(?,goods.category_id)', $category_id);
        }

        // 获取list 集合
        $skip = ($page-1)*$limit;
        $list = $list->select('goods.goods_discount','goods.goods_id','goods.goods_name','goods.goods_price','goods.goods_logo')
            ->where(array('goods_status'=>1))->orderBy('goods_id','desc')
            ->skip($skip)->limit($limit)->get()->toArray();

        foreach ($list as $k=>$val) {
            $list[$k]->privilegePrice = $list[$k]->goods_price;
            if ($val->goods_discount == 0) {
                continue;
            }
            $list[$k]->goods_price = number_format($val->goods_discount * $list[$k]->privilegePrice * 0.1,2);
        }
        echoToJson('Default code', $list);
    }

    public function getGoodsInfo() {
        $goods_id = $this->request->input('goodsId');
        if (!$goods_id) {
            echoToJson('No authority',array());
        }
        $res = DB::table('goods')->select('goods_discount','goods_brand','goods_name','goods_price','goods_logo','category_id','goods_status','goods_desc')->where(array('goods_id'=>$goods_id))->first();
        $goodsInfo = DB::table('goods_picture')->select('pic_url')->where(array('goods_id'=>$goods_id))->first();
        $goods_picture = array();
        if ($goodsInfo) {
            $pic = json_decode($goodsInfo->pic_url ,true);
            foreach ($pic as $k=>$v) {
                $goods_picture[] = $v['url'];
            }
        }

        $res->privilegePrice = $res->goods_price;
        $res->goods_price = $res->goods_discount != 0 ?number_format($res->goods_price * 0.1 * $res->goods_discount,2) : $res->goods_price;
        $res->goods_picture = $goods_picture;
        $res->buyRate = 103;
        echoToJson('Default code', $res);
    }
}
