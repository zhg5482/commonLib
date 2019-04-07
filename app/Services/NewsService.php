<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/4/4
 * Time: 下午6:50
 */
namespace App\Services;

use App\Models\NewsModel;

class NewsService {

    /**
     * @var NewsModel
     */
    public $newsModel;

    /**
     * @var int
     */
    private $page_num = 30;

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
        $data = $this->newsModel->getDataListByChannel($channelType,$skip);
        $res['channel'] = $channelType;
        $res['num'] = count($data);
        $res['list'] = $data;
        return $data;
    }

    /**
     * @param $search_key
     * @param $page
     * @return mixed
     */
    public function getDataListBySearch($search_key,$page) {
        $skip = (intval($page)-1) * $this->page_num;
        $data = $this->newsModel->getDataListBySearch($search_key,$skip);
        $res['keyword'] = $search_key;
        $res['num'] = count($data);
        $res['list'] = $data;
        return $res;
    }
}