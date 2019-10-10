<?php

namespace App\Http\Controllers\Vue;

use App\Http\Controllers\Controller;
use App\Lib\FastDfs\FastDfsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 上传文件模块
 * Class ClassifyController
 * @package App\Http\Controllers\Vue
 */
class UploadController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * 上传图片
     */
    public function uploadImage() {
        $file = $_FILES['file'];
        if (!$file) {
            echoToJson('No authority',array());
        }
        $ret = FastDfsHelper::getInstance()->uploadFile($file);
        if (!$ret) {
            echoToJson('No authority',array());
        }
        $fileName = config('app')['fileUrl'].'/'.$ret['group_name'].'/'.$ret['filename'];
        echoToJson('Default code',array('avatar'=>$fileName));
    }
}
