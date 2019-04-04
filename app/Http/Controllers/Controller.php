<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
