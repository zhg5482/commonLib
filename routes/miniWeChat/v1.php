<?php
/**
 * 小程序接口
 */


$router->group(['middleware' => ['throttle:60,1'],'namespace'=>'MiniWeChat'],function() use ($router) {

    $router->post('login', 'MiniWeChatController@login');
    $router->post('userAdd', 'MiniWeChatController@userAdd');
    $router->get('order', 'MiniWeChatController@order');
    $router->get('classify', 'MiniWeChatController@classify');
    $router->get('home/navBar', 'MiniWeChatController@homeNavBar');
    $router->get('home/banners', 'MiniWeChatController@homeBanners');
    $router->get('activity/brands', 'MiniWeChatController@activityBrands');
    $router->get('goods/getHotGoodsList', 'MiniWeChatController@getHotGoodsList');
    $router->get('goods/getGoodsInfo', 'MiniWeChatController@getGoodsInfo');

});
