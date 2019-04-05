<?php

namespace App\Http\Controllers;
use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * @var
     */
    public $newService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request,NewsService $newService)
    {
        //
        parent::__construct($request);
        $this->newService = $newService;
    }

    public function index() {

    }

    /**
     * 渠道接口
     */
    public function channel() {
        $res = $this->newService->getChannelsList();
        echoToJson('Request success',$res);
    }

    /**
     * 类别接口
     */
    public function channelType() {
        $channelType = $this->request->input('channel','');
        if (empty($channelType)) {
            echoToJson('Request success',array());
        }
        $page =  $this->request->input('page',1);
        $res =  $this->newService->getDataListByChannel($channelType,$page);
        echoToJson('Request success',$res);
    }

    /**
     * 搜素接口
     */
    public function channelSearch() {
        $search_key = $this->request->input('search_key','');
        if (empty($search_key)) {
            echoToJson('Request success',array());
        }
        $page =  $this->request->input('page',1);
        $res =  $this->newService->getDataListBySearch($search_key,$page);
        echoToJson('Request success',$res);
    }
}
