<?php
/**
 * v1 接口
 */

$router->post('/user/login','Vue\UsersController@login'); //用户登录
$router->post('/user/logout','Vue\UsersController@logout'); //退出登录

$router->group(['middleware' => ['throttle:60,1','vueVerification'],'namespace'=>'Vue'],function() use ($router){

    $router->get('/user/info','UsersController@info'); //用户信息
    $router->get('/user/getUserList','UsersController@getUserList'); //用户列表
    $router->post('/user/addUser','UsersController@addUser'); //添加用户
    $router->post('/user/updateUser','UsersController@updateUser'); //更新用户信息、状态
    $router->post('/user/updateAvatar','UsersController@updateAvatar'); //更新用户头像
    $router->get('/user/getUserInfoExtends/{id}','UsersController@getUserInfoExtends'); //获取用户扩展信息
    $router->post('/user/updateUserInfoExtends','UsersController@updateUserInfoExtends'); //更新用户扩展信息


    $router->get('/roles','RolesController@roles'); //权限
    $router->get('/getRoleById/{id}','RolesController@getRoleById'); //获取用户权限
    $router->post('/addRoles','RolesController@addRoles'); //添加权限
    $router->post('/deleteRoles/{id}','RolesController@deleteRoles'); //删除权限
    $router->post('/updateRoles/{id}','RolesController@updateRoles'); //更新权限


    $router->get('/system/getNewMessagesNum/{id}','SystemsController@getNewMessagesNum'); //获取最新消息
    $router->get('/system/getMessagesList/{id}','SystemsController@getMessagesList'); //获取消息
    $router->post('/system/updateMessageStatus','SystemsController@updateMessageStatus'); //更新消息


    $router->get('/classify/getClassify','ClassifyController@getClassify'); //分类
    $router->get('/classify/getClassifyList','ClassifyController@getClassifyList'); //分类选择
    $router->post('/classify/updateClassify','ClassifyController@updateClassify'); //更新分类状态
    $router->post('/classify/updateClassifyInfo/{id}','ClassifyController@updateClassifyInfo'); //更新分类信息
    $router->post('/classify/addClassify','ClassifyController@addClassify'); //添加分类
    $router->post('/classify/addGoodsInfo','ClassifyController@addGoodsInfo'); //添加商品
    $router->get('/classify/getClassifyJson','ClassifyController@getClassifyJson'); //获取分类基联
    $router->get('/classify/getGoodsList','ClassifyController@getGoodsList'); //获取商品列表
    $router->post('/classify/updateGoodsStatus','ClassifyController@updateGoodsStatus'); //更新商品状态
    $router->get('/classify/getGoodsInfoById/{id}','ClassifyController@getGoodsInfoById'); //获取单条商品信息


    $router->post('/upload/image','UploadController@uploadImage'); //上传图片

});
