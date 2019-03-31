<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/3/31
 * Time: 上午10:36
 */

/**
 * 中间件 signVerification
 */
$router->group(['middleware' => 'signVerification'], function () use ($router) {
    $router->get('index', [
        'as' => 'index', 'uses' => 'ExampleController@index'
    ]);
});

/**
 * throttle 访问频次控制 1 分钟 10 次
 */
$router->group(['middleware' => ['throttle:10,1']],function() use ($router){

    $router->get('throttleTest','ExampleController@throttleTest');

});