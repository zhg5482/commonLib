<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/4/4
 * Time: 下午6:50
 */
namespace App\Services;

use App\Lib\MongoDb\MongoDbHelper;
use App\Models\NewsModel;

class NewsService {

    /**
     * @var NewsModel
     */
    public $newsModel;

    /**
     * @var int
     */
    private $page_num = 20;

    /**
     * NewsService constructor.
     * @param NewsModel $newsModel
     */
    public function __construct(NewsModel $newsModel)
    {
        $this->newsModel = $newsModel;
    }

    /**
     * @return mixed
     */
    public function getChannelsList() {
        return $this->newsModel->getChannelsList();
    }

    /**
     * @param $channelType
     * @param $page
     * @return array
     */
    public function getDataListByChannel($channelType,$page) {
        $skip = (intval($page)-1) * $this->page_num;
        return MongoDbHelper::getInstance()->table('news')
            ->select('title','content','detail_url','source_title','channel','channel_id','image','create_time','type')
            ->where('channel',$channelType)
            ->orderBy('create_time','desc')
            ->skip($skip)
            ->take($this->page_num)
            ->get()
            ->toArray();
    }

    /**
     * @param $search_key
     * @param $page
     * @return array
     */
    public function getDataListBySearch($search_key,$page) {
        $skip = (intval($page)-1) * $this->page_num;
        return MongoDbHelper::getInstance()->table('news')
            ->select('title','content','detail_url','source_title','channel','channel_id','image','create_time','type')
            ->where('title','like','%'.$search_key.'%')
            ->orderBy('create_time','desc')
            ->skip($skip)
            ->take($this->page_num)
            ->get()
            ->toArray();
    }
}