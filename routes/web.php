<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


/**
 *  api 接口 路由 中间件  signVerification
 */
$router->group(['middleware' => 'signVerification'], function () use ($router) {
    $router->post('index', [
        'as' => 'index', 'uses' => 'ExampleController@index'
    ]);
});