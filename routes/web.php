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
 * 通用v1路由
 */
$router->group(['prefix'=>'api/v1'], function () use ($router) {
    require __DIR__.'/../routes/v1.php';
});



/**
 * 小程序路由
 */
$router->group(['prefix'=>'api/miniWeChat/v1'], function () use ($router) {
    require __DIR__ . '/../routes/miniWeChat/v1.php';
});



/**
 * Vue 路由
 */
$router->group(['prefix'=>'api/vue/v1'], function () use ($router) {
    require __DIR__ . '/../routes/vue/v1.php';
});

